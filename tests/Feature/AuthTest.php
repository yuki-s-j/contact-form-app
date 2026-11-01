<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    // 未ログインの場合、管理画面にアクセスできない
    public function test_guest_cannot_access_admin(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    // 正しいメールアドレスとパスワードでログインできる
    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin');

        $this->assertAuthenticatedAs($user);
    }

    // パスワードが間違っている場合、ログインできない
    public function test_user_cannot_login_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertRedirect('/login');

        $response->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    // ログイン中のユーザーがログアウトできる
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');

        $this->assertGuest();
    }

    // 正しい入力内容でユーザー登録できる
    public function test_user_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => '山田太郎',
            'email' => 'newuser@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/admin');

        $this->assertAuthenticated();

        $this->assertDatabaseHas('users', [
            'name' => '山田太郎',
            'email' => 'newuser@example.com',
        ]);
    }
}
