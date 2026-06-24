<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_contacts_index_returns_successfully(): void
    {
        Contact::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/contacts');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
                'meta',
            ])
            ->assertJsonCount(3, 'data');
    }

    public function test_contacts_index_can_filter_by_gender(): void
    {
        Contact::factory()->create([
            'gender' => 1,
        ]);

        Contact::factory()->create([
            'gender' => 2,
        ]);

        $response = $this->getJson('/api/v1/contacts?gender=1');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.gender', 1);
    }

    public function test_contacts_index_can_filter_by_category(): void
    {
        $targetCategory = Category::factory()->create();
        $otherCategory = Category::factory()->create();

        Contact::factory()->create([
            'category_id' => $targetCategory->id,
        ]);

        Contact::factory()->create([
            'category_id' => $otherCategory->id,
        ]);

        $response = $this->getJson('/api/v1/contacts?category_id='.$targetCategory->id);

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.category_id', $targetCategory->id);
    }

    public function test_contacts_index_can_filter_by_keyword(): void
    {
        Contact::factory()->create([
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'taro@example.com',
        ]);

        Contact::factory()->create([
            'first_name' => '花子',
            'last_name' => '佐藤',
            'email' => 'hanako@example.com',
        ]);

        $response = $this->getJson('/api/v1/contacts?keyword=山田');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.last_name', '山田');
    }

    public function test_contacts_index_returns_422_when_gender_is_invalid(): void
    {
        $response = $this->getJson('/api/v1/contacts?gender=9');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['gender']);
    }

    public function test_contact_show_returns_successfully(): void
    {
        $contact = Contact::factory()->create();

        $response = $this->getJson("/api/v1/contacts/{$contact->id}");

        $response->assertOk()
            ->assertJsonPath('data.id', $contact->id);
    }

    public function test_contact_show_returns_404_when_not_found(): void
    {
        $response = $this->getJson('/api/v1/contacts/99999');

        $response->assertNotFound()
            ->assertJson([
                'error' => 'お問い合わせが見つかりませんでした。',
            ]);
    }

    public function test_contact_can_be_created(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $response = $this->postJson('/api/v1/contacts', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区1-1-1',
            'building' => 'テストビル',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容',
            'tag_ids' => [$tag->id],
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('contacts', [
            'email' => 'test@example.com',
        ]);
    }

    public function test_contact_store_returns_422_when_gender_is_invalid(): void
    {
        $category = Category::factory()->create();

        $response = $this->postJson('/api/v1/contacts', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 9,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区1-1-1',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['gender']);
    }

    public function test_contact_can_be_updated(): void
    {
        $category = Category::factory()->create();
        $contact = Contact::factory()->create();

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", [
            'first_name' => '更新',
            'last_name' => '太郎',
            'gender' => 2,
            'email' => 'update@example.com',
            'tel' => '08012345678',
            'address' => '愛知県阿久比町',
            'building' => '更新ビル',
            'category_id' => $category->id,
            'detail' => '更新テスト',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'email' => 'update@example.com',
        ]);
    }

    public function test_contact_update_syncs_tags(): void
    {
        $category = Category::factory()->create();

        $oldTag = Tag::factory()->create();
        $newTag = Tag::factory()->create();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $contact->tags()->attach($oldTag->id);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", [
            'first_name' => '更新',
            'last_name' => '太郎',
            'gender' => 2,
            'email' => 'update@example.com',
            'tel' => '08012345678',
            'address' => '愛知県阿久比町',
            'building' => '更新ビル',
            'category_id' => $category->id,
            'detail' => '更新テスト',
            'tag_ids' => [$newTag->id],
        ]);

        $response->assertOk();

        $this->assertDatabaseMissing('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $oldTag->id,
        ]);

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $newTag->id,
        ]);
    }

    public function test_contact_update_returns_404_when_contact_not_found(): void
    {
        $category = Category::factory()->create();

        $response = $this->putJson('/api/v1/contacts/99999', [
            'first_name' => '更新',
            'last_name' => '太郎',
            'gender' => 2,
            'email' => 'update@example.com',
            'tel' => '08012345678',
            'address' => '愛知県阿久比町',
            'category_id' => $category->id,
            'detail' => '更新テスト',
        ]);

        $response->assertNotFound()
            ->assertJson([
                'error' => 'お問い合わせが見つかりませんでした。',
            ]);
    }

    public function test_contact_can_be_deleted(): void
    {
        $contact = Contact::factory()->create();

        $response = $this->deleteJson("/api/v1/contacts/{$contact->id}");

        $response->assertNoContent();

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }

    public function test_contact_delete_returns_404_when_contact_not_found(): void
    {
        $response = $this->deleteJson('/api/v1/contacts/99999');

        $response->assertNotFound()
            ->assertJson([
                'error' => 'お問い合わせが見つかりませんでした。',
            ]);
    }
}
