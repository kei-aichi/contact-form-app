<?php

namespace Tests\Unit;

use App\Http\Requests\UpdateTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateTagRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_tag_name_passes_validation(): void
    {
        $tag = Tag::factory()->create();

        $request = new UpdateTagRequest;

        $request->setRouteResolver(function () use ($tag) {
            return new class($tag)
            {
                public function __construct(private Tag $tag) {}

                public function parameter(string $key): Tag
                {
                    return $this->tag;
                }
            };
        });

        $validator = Validator::make([
            'name' => '更新後タグ',
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_same_tag_name_passes_validation(): void
    {
        $tag = Tag::factory()->create([
            'name' => '質問',
        ]);

        $request = new UpdateTagRequest;

        $request->setRouteResolver(function () use ($tag) {
            return new class($tag)
            {
                public function __construct(private Tag $tag) {}

                public function parameter(string $key): Tag
                {
                    return $this->tag;
                }
            };
        });

        $validator = Validator::make([
            'name' => '質問',
        ], $request->rules());

        $this->assertTrue($validator->passes());
    }

    public function test_tag_name_is_required(): void
    {
        $tag = Tag::factory()->create();

        $request = new UpdateTagRequest;

        $request->setRouteResolver(function () use ($tag) {
            return new class($tag)
            {
                public function __construct(private Tag $tag) {}

                public function parameter(string $key): Tag
                {
                    return $this->tag;
                }
            };
        });

        $validator = Validator::make([
            'name' => '',
        ], $request->rules());

        $this->assertFalse($validator->passes());
    }

    public function test_tag_name_must_be_50_characters_or_less(): void
    {
        $tag = Tag::factory()->create();

        $request = new UpdateTagRequest;

        $request->setRouteResolver(function () use ($tag) {
            return new class($tag)
            {
                public function __construct(private Tag $tag) {}

                public function parameter(string $key): Tag
                {
                    return $this->tag;
                }
            };
        });

        $validator = Validator::make([
            'name' => str_repeat('あ', 51),
        ], $request->rules());

        $this->assertFalse($validator->passes());
    }

    public function test_tag_name_must_be_unique_except_current_tag(): void
    {
        $currentTag = Tag::factory()->create([
            'name' => '質問',
        ]);

        Tag::factory()->create([
            'name' => '要望',
        ]);

        $request = new UpdateTagRequest;

        $request->setRouteResolver(function () use ($currentTag) {
            return new class($currentTag)
            {
                public function __construct(private Tag $tag) {}

                public function parameter(string $key): Tag
                {
                    return $this->tag;
                }
            };
        });

        $validator = Validator::make([
            'name' => '要望',
        ], $request->rules());

        $this->assertFalse($validator->passes());
    }
}
