<?php

namespace Tests\Unit\Api\V1;

use App\Http\Requests\Api\V1\StoreContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_contact_data_with_tags_passes_validation(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $request = new StoreContactRequest;

        $validator = Validator::make([
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区1-1-1',
            'building' => 'テストビル101',
            'category_id' => $category->id,
            'detail' => '商品の配送日について',
            'tag_ids' => [$tag->id],
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_invalid_gender_is_rejected(): void
    {
        $request = new StoreContactRequest;

        $validator = Validator::make([
            'gender' => 9,
        ], $request->rules());

        $this->assertFalse($validator->passes());
    }

    public function test_invalid_category_id_is_rejected(): void
    {
        $request = new StoreContactRequest;

        $validator = Validator::make([
            'category_id' => 999,
        ], $request->rules());

        $this->assertFalse($validator->passes());
    }

    public function test_invalid_tag_id_is_rejected(): void
    {
        $request = new StoreContactRequest;

        $validator = Validator::make([
            'tag_ids' => [999],
        ], $request->rules());

        $this->assertFalse($validator->passes());
    }

    public function test_invalid_tel_is_rejected(): void
    {
        $request = new StoreContactRequest;

        $validator = Validator::make([
            'tel' => '090-1234-5678',
        ], $request->rules());

        $this->assertFalse($validator->passes());
    }

    public function test_required_fields_are_rejected_when_missing(): void
    {
        $request = new StoreContactRequest;

        $validator = Validator::make([], $request->rules());

        $this->assertFalse($validator->passes());
    }
}
