<?php

namespace Tests\Unit;

use App\Http\Requests\IndexContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class IndexContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_search_conditions_pass_validation(): void
    {
        $category = Category::factory()->create();

        $request = new IndexContactRequest;

        $validator = Validator::make([
            'keyword' => '山田',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2026-06-21',
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
}
