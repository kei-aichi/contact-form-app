<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_export_csv(): void
    {
        $user = User::factory()->create();

        Contact::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/contacts/export');

        $response->assertStatus(200);

        $response->assertHeader(
            'content-disposition'
        );
    }

    public function test_guest_cannot_export_csv(): void
    {
        $response = $this->get('/contacts/export');

        $response->assertRedirect('/login');
    }

    public function test_contacts_can_be_exported_with_search_conditions(): void
    {
        $user = User::factory()->create();

        Contact::factory()->create([
            'first_name' => '太郎',
        ]);

        Contact::factory()->create([
            'first_name' => '花子',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/contacts/export?keyword=太郎');

        $response->assertStatus(200);

        $content = $response->streamedContent();

        $this->assertStringContainsString('太郎', $content);
        $this->assertStringNotContainsString('花子', $content);
    }

    public function test_contacts_are_exported_in_latest_order_when_no_filter_is_specified(): void
    {
        $user = User::factory()->create();

        Contact::factory()->create([
            'first_name' => '古い',
            'created_at' => now()->subDay(),
        ]);

        Contact::factory()->create([
            'first_name' => '新しい',
            'created_at' => now(),
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/contacts/export');

        $response->assertStatus(200);

        $content = $response->streamedContent();

        $newPosition = strpos($content, '新しい');
        $oldPosition = strpos($content, '古い');

        $this->assertNotFalse($newPosition);
        $this->assertNotFalse($oldPosition);

        $this->assertLessThan($oldPosition, $newPosition);
    }
}
