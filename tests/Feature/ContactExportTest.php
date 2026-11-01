<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactExportTest extends TestCase
{
    use RefreshDatabase;

    // 正常系：ログイン済みユーザーがCSVをダウンロードできる
    public function test_authenticated_user_can_export_contacts(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/contacts/export');

        $response->assertStatus(200);
        $response->assertDownload('contacts.csv');
    }

    // 正常系：CSVに指定された10項目のヘッダーが出力される
    public function test_csv_contains_correct_headers(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/contacts/export');

        $response->assertStatus(200);

        $csv = $response->streamedContent();

        // UTF-8のBOMを取り除く
        $csv = preg_replace('/^\xEF\xBB\xBF/', '', $csv);

        // CSVの1行目を取得する
        $firstLine = strtok($csv, "\n");

        $headers = str_getcsv($firstLine);

        $this->assertSame([
            'ID',
            '氏名',
            '性別',
            'メールアドレス',
            '電話番号',
            '住所',
            '建物名',
            'カテゴリー名',
            'お問い合わせ内容',
            '作成日時',
        ], $headers);
    }

    // 正常系：お問い合わせデータがCSVに正しく出力される
    public function test_csv_contains_contact_data(): void
    {
        $user = User::factory()->create();

        // テスト用カテゴリーを作成
        $category = Category::create([
            'content' => '商品について',
        ]);

        // テスト用お問い合わせデータを作成
        $contact = Contact::create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区1-1-1',
            'building' => '渋谷ビル301',
            'category_id' => $category->id,
            'detail' => '商品の詳細を教えてください。',
        ]);

        // CSVをダウンロード
        $response = $this->actingAs($user)->get('/contacts/export');

        $response->assertStatus(200);

        // CSVの内容を取得
        $csv = $response->streamedContent();

        // CSVに必要なデータが含まれているか確認
        $this->assertStringContainsString('山田 太郎', $csv);
        $this->assertStringContainsString('男性', $csv);
        $this->assertStringContainsString('yamada@example.com', $csv);
        $this->assertStringContainsString('09012345678', $csv);
        $this->assertStringContainsString('商品について', $csv);
        $this->assertStringContainsString('商品の詳細を教えてください。', $csv);
    }

    // 正常系：性別で検索した結果だけがCSVに出力される
    public function test_csv_export_filters_contacts_by_gender(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'content' => '商品について',
        ]);

        // 男性のお問い合わせを登録
        Contact::create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'male@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区1-1-1',
            'building' => '',
            'category_id' => $category->id,
            'detail' => '男性のテストデータ',
        ]);

        // 女性のお問い合わせを登録
        Contact::create([
            'first_name' => '佐藤',
            'last_name' => '花子',
            'gender' => 2,
            'email' => 'female@example.com',
            'tel' => '08012345678',
            'address' => '愛知県名古屋市1-1-1',
            'building' => '',
            'category_id' => $category->id,
            'detail' => '女性のテストデータ',
        ]);

        // 男性（gender=1）でCSV出力
        $response = $this->actingAs($user)->get('/contacts/export?gender=1');

        $response->assertStatus(200);

        $csv = $response->streamedContent();

        // 男性のデータが含まれること
        $this->assertStringContainsString('male@example.com', $csv);

        // 女性のデータが含まれないこと
        $this->assertStringNotContainsString('female@example.com', $csv);
    }

    // 正常系：検索条件なしで全件を新着順にCSV出力できる
    public function test_csv_export_all_contacts_in_latest_order(): void
    {
        $user = User::factory()->create();

        $category = Category::create([
            'content' => '商品について',
        ]);

        // 古いお問い合わせデータ
        $oldContact = Contact::create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'old@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区1-1-1',
            'building' => '',
            'category_id' => $category->id,
            'detail' => '古いお問い合わせ',
        ]);

        // 作成日時を設定
        $oldContact->created_at = '2026-10-01 10:00:00';
        $oldContact->save();

        // 新しいお問い合わせデータ
        $newContact = Contact::create([
            'first_name' => '佐藤',
            'last_name' => '花子',
            'gender' => 2,
            'email' => 'new@example.com',
            'tel' => '08012345678',
            'address' => '愛知県名古屋市1-1-1',
            'building' => '',
            'category_id' => $category->id,
            'detail' => '新しいお問い合わせ',
        ]);

        // 作成日時を設定
        $newContact->created_at = '2026-11-01 10:00:00';
        $newContact->save();

        // 検索条件なしでCSVを出力
        $response = $this->actingAs($user)->get('/contacts/export');

        $response->assertStatus(200);

        $csv = $response->streamedContent();

        // 2件とも出力されていること
        $this->assertStringContainsString('old@example.com', $csv);
        $this->assertStringContainsString('new@example.com', $csv);

        // 新しいデータが古いデータより先に出力されていること
        $newPosition = strpos($csv, 'new@example.com');
        $oldPosition = strpos($csv, 'old@example.com');

        $this->assertLessThan($oldPosition, $newPosition);
    }

    // 異常系：未ログインユーザーはCSVをダウンロードできない
    public function test_guest_cannot_export_contacts(): void
    {
        // ログインせずにCSV出力URLへアクセス
        $response = $this->get('/contacts/export');

        // ログイン画面へリダイレクトされること
        $response->assertRedirect('/login');
    }

    // 異常系：不正な性別を指定するとCSVを出力できない
    public function test_csv_export_rejects_invalid_gender(): void
    {
        $user = User::factory()->create();

        // 存在しない性別（gender=9）を指定
        $response = $this->actingAs($user)
            ->get('/contacts/export?gender=9');

        // バリデーションエラーが発生すること
        $response->assertSessionHasErrors('gender');

        // CSVダウンロードではなくリダイレクトされること
        $response->assertStatus(302);
    }

    // 異常系：存在しないカテゴリーIDではCSVを出力できない
    public function test_csv_export_rejects_invalid_category(): void
    {
        $user = User::factory()->create();

        // 存在しないカテゴリーIDを指定
        $response = $this->actingAs($user)
            ->get('/contacts/export?category_id=999999');

        // カテゴリーIDのバリデーションエラーを確認
        $response->assertSessionHasErrors('category_id');

        // CSVを出力せずリダイレクトされること
        $response->assertStatus(302);
    }
}
