<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    // ログイン済みユーザーがタグを登録できる
    public function test_admin_can_create_tag(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/tags', [
            'name' => '重要',
        ]);

        $response->assertRedirect('/admin');

        $this->assertDatabaseHas('tags', [
            'name' => '重要',
        ]);
    }

    // ログイン済みユーザーがタグを編集できる
    public function test_admin_can_update_tag(): void
    {
        $user = User::factory()->create();

        $tag = Tag::create([
            'name' => '重要',
        ]);

        $response = $this->actingAs($user)->put("/admin/tags/{$tag->id}", [
            'name' => '至急',
        ]);

        $response->assertRedirect('/admin');

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => '至急',
        ]);

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
            'name' => '重要',
        ]);
    }

    // ログイン済みユーザーがタグを削除できる
    public function test_admin_can_delete_tag(): void
    {
        $user = User::factory()->create();

        $tag = Tag::create([
            'name' => '重要',
        ]);

        $response = $this->actingAs($user)->delete("/admin/tags/{$tag->id}");

        $response->assertRedirect('/admin');

        $this->assertDatabaseMissing('tags', [
            'id' => $tag->id,
        ]);
    }

    // 同じ名前のタグを重複登録できない
    public function test_admin_cannot_create_duplicate_tag(): void
    {
        $user = User::factory()->create();

        Tag::create([
            'name' => '重要',
        ]);

        $response = $this->actingAs($user)->from('/admin')->post('/admin/tags', [
            'name' => '重要',
        ]);

        $response->assertRedirect('/admin');

        $response->assertSessionHasErrors('name');

        $this->assertDatabaseCount('tags', 1);
    }
}
