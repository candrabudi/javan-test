<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Family;
use Validator;
class FamilyController extends Controller
{
    public function index()
    {
        $families = Family::where('parent_id', null)
            ->with('children')->get();

        return response()->json([
            'meta' => [
                'status' => 'success', 
                'code' => 200, 
                'message' => 'Success get list families'
            ],
            'data' => $families
        ]);
    }
    
    public function detail($id)
    {
        $family = Family::where('id', $id)
            ->where('parent_id', null)
            ->with('children')
            ->first();

        if(!$family){
            return response()->json([
                'meta' => [
                    'status' => 'failed', 
                    'code' => 400, 
                    'message' => 'sorry no data family'
                ],
                'data' => null
            ]);
        }

        return response()->json([
            'meta' => [
                'status' => 'success', 
                'code' => 200, 
                'message' => 'Success get detail family'
            ],
            'data' => $family
        ]);
    }

    public function store(Request $request)
    {
        $familyData = $request->input('family');


        $rootPerson = $this->storePersonAndFamily($familyData);

        return response()->json([
            'meta' => [
                'status' => 'success', 
                'code' => 201, 
                'message' => 'Success store data families'
            ],
            'data' => null
        ], 201);
    }

    private function storePersonAndFamily($data, $parent = null)
    {
        $personData = [
            'name' => $data['name'],
            'gender' => $data['gender'],
            'parent_id' => $parent ? $parent->id : null,
        ];

        $person = Family::create($personData);

        if (isset($data['children'])) {
            foreach ($data['children'] as $childData) {
                $this->storePersonAndFamily($childData, $person);
            }
        }

        return $person;
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'family.id' => 'required|exists:families,id',
            'family.name' => 'required|string',
            'family.gender' => 'required|in:Laki-laki,Perempuan',
            'family.children.*.id' => 'sometimes|required|exists:families,id',
            'family.children.*.name' => 'required|string',
            'family.children.*.gender' => 'required|in:Laki-laki,Perempuan',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'code' => 422,
                    'message' => 'Validation error',
                    'errors' => $validator->errors(),
                ],
                'data' => null,
            ], 422);
        }

        $familyData = $request->input('family');

        if ($id != $familyData['id']) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'code' => 422,
                    'message' => 'Validation error',
                    'errors' => ['id' => ['The provided ID does not match the request data.']],
                ],
                'data' => null,
            ], 422);
        }

        $this->updatePersonAndFamily($familyData);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'code' => 200,
                'message' => 'Success update data families',
            ],
            'data' => null,
        ], 200);
    }

    private function updatePersonAndFamily($data)
    {
        $familyId = $data['id'];

        $familyData = [
            'name' => $data['name'],
            'gender' => $data['gender'],
        ];

        Family::where('id', $familyId)->update($familyData);

        if (isset($data['children'])) {
            foreach ($data['children'] as $childData) {
                $this->updatePersonAndFamily($childData);
            }
        }
    }

    public function delete(Request $request, $id)
    {
        $family = Family::find($id);

        if (!$family) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'Family not found',
                ],
                'data' => null,
            ], 404);
        }

        if ($family->parent_id !== null) {
            return response()->json([
                'meta' => [
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'Cannot delete family with parent',
                ],
                'data' => null,
            ], 400);
        }

        $this->deleteFamilyAndDescendants($family);

        return response()->json([
            'meta' => [
                'status' => 'success',
                'code' => 200,
                'message' => 'Success delete data families',
            ],
            'data' => null,
        ], 200);
    }

    private function deleteFamilyAndDescendants(Family $family)
    {
        foreach ($family->children as $child) {
            $this->deleteFamilyAndDescendants($child);
        }

        $family->delete();
    }

    public function testUpdate()
    {

        $family = Family::create([
            'name' => 'John',
            'gender' => 'Laki-laki',
        ]);

        $updateData = [
            'family' => [
                'id' => $family->id,
                'name' => 'Updated Name',
                'gender' => 'Perempuan',
                'children' => [
                    [
                        'id' => $family->id,
                        'name' => 'Updated Child Name',
                        'gender' => 'Laki-laki',
                    ],
                ],
            ],
        ];

        $response = $this->putJson("/families/update/{$family->id}", $updateData);

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'meta' => ['status', 'code', 'message'],
            'data' => null,
        ]);

        $this->assertDatabaseHas('families', [
            'id' => $family->id,
            'name' => 'Updated Name',
            'gender' => 'Perempuan',
        ]);

        $this->assertDatabaseHas('families', [
            'id' => $family->id,
            'name' => 'Updated Child Name',
            'gender' => 'Laki-laki',
        ]);
    }

}
