<?php

namespace Tests\Feature\Controllers\Api\v1;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class ProductControllerTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('migrate');
    }

    public function test_index_successs_endpoint_() 
    {
        Product::factory()->hasVariants(3)->count(5)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200);
    }

    public function test_index_endpoint_paginated_products() 
    {
        Product::factory()->hasVariants(3)->count(5)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'price',
                    'variants' => [
                        '*' => [
                            'id',
                            'product_id',
                            'color',
                            'size',
                            'stock_quantity'
                        ]
                    ]
                ]
            ]
        ]);
    }
}
