<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    // お問い合わせ入力画面が表示される
    public function test_contact_from_is_displayed(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    // お問い合わせ内容が正常に登録される
    public function test_contact_can_be_stored(): void
    {
        // テスト用のカテゴリーを作成
        $category = Category::create([
            'content' => '商品について',
        ]);

        // お問い合わせ内容を送信
        $response = $this->post('/contacts', [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'user@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区1-1-1',
            'building' => '渋谷ビル301',
            'category_id' => $category->id,
            'detail' => 'お問い合わせのテストです。',
        ]);

        // サンクス画面へリダイレクトされる
        $response->assertRedirect('/thanks');

        // DBにお問い合わせ内容が保存される
        $this->assertDatabaseHas('contacts', [
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'user@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区1-1-1',
            'building' => '渋谷ビル301',
        ]);
    }

    // 必須項目が未入力の場合、登録されないこと
    public function test_contact_cannot_be_stored_without_required_fields(): void
    {
        $response = $this->from('/')->post('/contacts', []);

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

        $this->assertDatabaseCount('contacts', 0);
    }

    // 確認画面への送信時に必須項目が未入力ならエラーになること
    public function test_contact_confirmation_requires_fields(): void
    {
        $response = $this->from('/')->post('/contacts/confirm', []);

        $response->assertRedirect('/');

        $response->assertSessionHasErrors([
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel1',
            'tel2',
            'tel3',
            'address',
            'category_id',
            'detail',
        ]);
    }

    // 正しい入力内容で確認画面が表示されること
    public function test_contact_confirmation_is_displayed(): void
    {
        $category = Category::create([
            'content' => '商品について',
        ]);

        $response = $this->post('/contacts/confirm', [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'user@example.com',
            'tel1' => '090',
            'tel2' => '1234',
            'tel3' => '5678',
            'address' => '東京都渋谷区1-1-1',
            'building' => '渋谷ビル301',
            'category_id' => $category->id,
            'detail' => 'お問い合わせのテストです。',
        ]);

        $response->assertStatus(200);

        $response->assertViewIs('contact.confirm');

        $response->assertViewHas('validated', function ($validated) {
            return $validated['tel'] === '09012345678';
        });
    }
}
