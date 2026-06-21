<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_detail_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $contact = Contact::factory()->create([
            'first_name' => '太郎',
            'last_name' => '山田',
            'email' => 'test@example.com',
        ]);

        $response = $this
            ->actingAs($user)
            ->get("/admin/contacts/{$contact->id}");

        $response->assertStatus(200);

        $response->assertSee('山田');
        $response->assertSee('太郎');
        $response->assertSee('test@example.com');
    }

    public function test_contact_can_be_deleted(): void
    {
        $user = User::factory()->create();

        $contact = Contact::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete("/admin/contacts/{$contact->id}");

        $response->assertRedirect('/admin');

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }
}
