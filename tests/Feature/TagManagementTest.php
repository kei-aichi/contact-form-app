<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_tag(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post('/admin/tags', [
                'name' => '新規タグ',
            ]);

        $response->assertRedirect('/admin');

        $this->assertDatabaseHas('tags', [
            'name' => '新規タグ',
        ]);
    }

    public function test_authenticated_user_can_update_tag(): void
    {
        $user = User::factory()->create();

        $tag = Tag::factory()->create([
            'name' => '変更前',
        ]);

        $response = $this
            ->actingAs($user)
            ->put("/admin/tags/{$tag->id}", [
                'name' => '変更後',
            ]);

        $response->assertRedirect('/admin');

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => '変更後',
        ]);
    }

    public function test_authenticated_user_can_delete_tag(): void
    {
        $user = User::factory()->create();

        $tag = Tag::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete("/admin/tags/{$tag->id}");

        $response->assertRedirect('/admin');

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);
    }

    public function test_guest_cannot_manage_tags(): void
    {
        $tag = Tag::factory()->create();

        $this->post('/admin/tags', [
            'name' => '新規タグ',
        ])->assertRedirect('/login');

        $this->put("/admin/tags/{$tag->id}", [
            'name' => '変更後',
        ])->assertRedirect('/login');

        $this->delete("/admin/tags/{$tag->id}")
            ->assertRedirect('/login');
    }
}
