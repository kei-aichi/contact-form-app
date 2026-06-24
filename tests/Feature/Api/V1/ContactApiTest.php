<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Contact;
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
}
