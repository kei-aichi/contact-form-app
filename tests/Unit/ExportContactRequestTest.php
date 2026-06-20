<?php

namespace Tests\Unit;

use App\Http\Requests\ExportContactRequest;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ExportContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_export_conditions_pass_validation(): void
    {
        $category = Category::factory()->create();

        $request = new ExportContactRequest;

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
        $request = new ExportContactRequest;

        $validator = Validator::make([
            'gender' => 9,
        ], $request->rules());

        $this->assertFalse($validator->passes());
    }

    public function test_non_existing_category_is_rejected(): void
    {
        $request = new ExportContactRequest;

        $validator = Validator::make([
            'category_id' => 9999,
        ], $request->rules());

        $this->assertFalse($validator->passes());
    }
}
