<?php

namespace Tests\Feature;

use App\Models\Family;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FamilyControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testIndex()
    {
        $response = $this->get('/api/families');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'meta' => ['status', 'code', 'message'],
            'data' => [],
        ]);
    }

    public function testStore()
    {
        $data = [
            'family' => [
                'name' => 'John',
                'gender' => 'Laki-laki',
                'children' => [
                    [
                        'name' => 'Doe',
                        'gender' => 'Perempuan',
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/families/store', $data);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'meta' => ['status', 'code', 'message'],
            'data' => [],
        ]);

        $this->assertDatabaseHas('families', [
            'name' => 'John',
            'gender' => 'Laki-laki',
        ]);

        $this->assertDatabaseHas('families', [
            'name' => 'Doe',
            'gender' => 'Perempuan',
        ]);
    }
}
