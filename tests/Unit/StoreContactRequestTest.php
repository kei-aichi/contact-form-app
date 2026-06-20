<?php

namespace Tests\Unit;

use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_data_with_tags_passes_validation(): void
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
            'address' => '東京都',
            'building' => 'テストビル',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容',
            'tag_ids' => [$tag->id],
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_invalid_phone_number_is_rejected(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $request = new StoreContactRequest;

        $validator = Validator::make([
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => 'abcd',
            'address' => '東京都',
            'building' => 'テストビル',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容',
            'tag_ids' => [$tag->id],
        ], $request->rules());

        $this->assertFalse($validator->passes());
    }
}
