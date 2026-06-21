<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_keyword_search_works(): void
    {
        $user = User::factory()->create();

        Contact::factory()->create([
            'first_name' => '太郎',
            'last_name' => '山田',
        ]);

        Contact::factory()->create([
            'first_name' => '花子',
            'last_name' => '佐藤',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin?keyword=山田');

        $response->assertStatus(200);

        $response->assertSee('山田');
        $response->assertDontSee('佐藤');
    }

    public function test_gender_search_works(): void
    {
        $user = User::factory()->create();

        Contact::factory()->create([
            'first_name' => '太郎',
            'gender' => 1,
        ]);

        Contact::factory()->create([
            'first_name' => '花子',
            'gender' => 2,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin?gender=1');

        $response->assertStatus(200);

        $response->assertSee('太郎');
        $response->assertDontSee('花子');
    }

    public function test_category_search_works(): void
    {
        $user = User::factory()->create();

        $targetCategory = Category::factory()->create([
            'content' => '商品トラブル',
        ]);

        $otherCategory = Category::factory()->create([
            'content' => 'その他',
        ]);

        Contact::factory()->create([
            'first_name' => '太郎',
            'category_id' => $targetCategory->id,
        ]);

        Contact::factory()->create([
            'first_name' => '花子',
            'category_id' => $otherCategory->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin?category_id='.$targetCategory->id);

        $response->assertStatus(200);

        $response->assertSee('太郎');
        $response->assertDontSee('花子');
    }

    public function test_date_search_works(): void
    {
        $user = User::factory()->create();

        Contact::factory()->create([
            'first_name' => '太郎',
            'created_at' => '2026-06-21 10:00:00',
        ]);

        Contact::factory()->create([
            'first_name' => '花子',
            'created_at' => '2026-06-20 10:00:00',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/admin?date=2026-06-21');

        $response->assertStatus(200);

        $response->assertSee('太郎');
        $response->assertDontSee('花子');
    }

    public function test_contacts_are_paginated_by_7(): void
    {
        $user = User::factory()->create();

        Contact::factory()->count(8)->create();

        $response = $this
            ->actingAs($user)
            ->get('/admin');

        $response->assertStatus(200);

        $response->assertSee('?page=2', false);
    }
}
