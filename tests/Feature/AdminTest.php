<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    // ログイン済みユーザーが管理画面を表示できる
    public function test_authenticated_user_can_access_admin(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(200);
        $response->assertViewIs('admin.index');
    }

    // 名前で検索すると、該当するお問い合わせだけが表示される
    public function test_admin_can_search_contacts_by_name(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'content' => '商品について',
        ]);

        $contact1 = Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'yamada@example.com',
        ]);

        $contact2 = Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => '佐藤',
            'last_name' => '花子',
            'email' => 'sato@example.com',
        ]);

        $response = $this->actingAs($user)->get('/admin?keyword=山田');

        $response->assertStatus(200);

        $response->assertViewHas('contacts', function ($contacts) use ($contact1, $contact2) {
            return $contacts->contains('id', $contact1->id)
                && ! $contacts->contains('id', $contact2->id);
        });
    }

    // お問い合わせの詳細画面が表示される
    public function test_admin_can_view_contact_details(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'content' => '商品について',
        ]);

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
        ]);

        $response = $this->actingAs($user)
            ->get("/admin/contacts/{$contact->id}");

        $response->assertStatus(200);

        $response->assertViewIs('admin.show');

        $response->assertViewHas('contact', function ($viewContact) use ($contact) {
            return $viewContact->id === $contact->id;
        });
    }

    // 管理者がお問い合わせを削除できる
    public function test_admin_can_delete_contact(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'content' => '商品について',
        ]);

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($user)->delete("/admin/contacts/{$contact->id}");

        $response->assertRedirect('/admin');

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }
}
