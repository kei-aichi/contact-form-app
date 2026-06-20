<?php

namespace Tests\Unit;

use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreTagRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_tag_name_passes_validation(): void
    {
        $request = new StoreTagRequest;

        $validator = Validator::make([
            'name' => '新機能の要望',
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_tag_name_is_required(): void
    {
        $request = new StoreTagRequest;

        $validator = Validator::make([
            'name' => '',
        ], $request->rules());

        $this->assertFalse($validator->passes());
    }

    public function test_tag_name_must_be_50_characters_or_less(): void
    {
        $request = new StoreTagRequest;

        $validator = Validator::make([
            'name' => str_repeat('あ', 51),
        ], $request->rules());

        $this->assertFalse($validator->passes());
    }

    public function test_tag_name_must_be_unique(): void
    {
        Tag::factory()->create([
            'name' => '質問',
        ]);

        $request = new StoreTagRequest;

        $validator = Validator::make([
            'name' => '質問',
        ], $request->rules());

        $this->assertFalse($validator->passes());
    }
}
