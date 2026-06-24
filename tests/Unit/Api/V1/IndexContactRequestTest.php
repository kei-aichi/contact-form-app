<?php

namespace Tests\Unit\Api\V1;

use App\Http\Requests\Api\V1\IndexContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndexContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_search_parameters_pass_validation(): void
    {
        $category = Category::factory()->create();

        $request = new IndexContactRequest;

        $validator = Validator::make([
            'keyword' => '山田',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2026-06-24',
            'page' => 1,
            'per_page' => 20,
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_invalid_gender_is_rejected(): void
    {
        $request = new IndexContactRequest;

        $validator = Validator::make([
            'gender' => 9,
        ], $request->rules());

        $this->assertFalse($validator->passes());
    }

    public function test_invalid_category_id_is_rejected(): void
    {
        $request = new IndexContactRequest;

        $validator = Validator::make([
            'category_id' => 999,
        ], $request->rules());

        $this->assertFalse($validator->passes());
    }

    public function test_invalid_date_is_rejected(): void
    {
        $request = new IndexContactRequest;

        $validator = Validator::make([
            'date' => 'abc',
        ], $request->rules());

        $this->assertFalse($validator->passes());
    }

    public function test_invalid_per_page_is_rejected(): void
    {
        $request = new IndexContactRequest;

        $validator = Validator::make([
            'per_page' => 101,
        ], $request->rules());

        $this->assertFalse($validator->passes());
    }
}
