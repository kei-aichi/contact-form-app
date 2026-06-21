<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactConfirmTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_confirm_page_is_displayed(): void
    {
        $category = Category::factory()->create([
            'content' => '商品のお届けについて',
        ]);

        $response = $this->post('/contacts/confirm', [
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都',
            'building' => 'テストビル',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容',
        ]);

        $response->assertStatus(200);

        $response->assertSee('山田');
        $response->assertSee('太郎');
        $response->assertSee('test@example.com');
        $response->assertSee('商品のお届けについて');
        $response->assertSee('お問い合わせ内容');
    }

    public function test_contact_confirm_redirects_back_with_errors_when_validation_fails(): void
    {
        $response = $this->from('/')
            ->post('/contacts/confirm', [
                'first_name' => '',
                'last_name' => '',
                'gender' => '',
                'email' => '',
                'tel' => '',
                'address' => '',
                'category_id' => '',
                'detail' => '',
            ]);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors([
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'category_id',
            'detail',
        ]);
    }
}
