<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Http\Requests\Api\V1\IndexContactRequest;
use App\Http\Requests\Api\V1\StoreContactRequest;
use App\Http\Requests\Api\V1\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    // お問い合わせ一覧をJSON形式で取得
    public function index(IndexContactRequest $request)
    {
        // カテゴリー・タグ情報と一緒にお問い合わせを取得する準備
        $query = Contact::with(['category', 'tags']);

        // 姓・名・メールアドレスのキーワード検索
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');

            $query->where(function ($q) use ($keyword) {
                $q->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        // 性別による絞り込み
        if ($request->filled('gender')) {
            $query->where('gender', $request->input('gender'));
        }

        // カテゴリーによる絞り込み
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // 日付による絞り込み
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        // 1ページあたりの取得件数（指定がなければ20件）
        $perPage = $request->input('per_page', 20);

        // 新着順で指定件数ずつ取得
        $contacts = $query->latest()->paginate($perPage);

        return ContactResource::collection($contacts);
    }

    // お問い合わせ詳細をJSON形式で取得
    public function show(Contact $contact)
    {
        // カテゴリー・タグ情報を読み込む
        $contact->load(['category', 'tags']);

        return new ContactResource($contact);
    }

    // お問い合わせを新規登録する
    public function store(StoreContactRequest $request)
    {
        // バリデーション済みのデータを取得
        $validated = $request->validated();

        // タグIDを取り出す
        $tagIds = $validated['tag_ids'] ?? [];

        // お問い合わせ本体を登録
        $contact = Contact::create(
            collect($validated)->except('tag_ids')->all()
        );

        // お問い合わせとタグを関連付ける
        $contact->tags()->sync($tagIds);

        // カテゴリー・タグ情報を読み込む
        $contact->load(['category', 'tags']);

        // 登録したデータをJSON形式で返す
        return (new ContactResource($contact))->response()->setStatusCode(201);
    }

    // お問い合わせを更新する
    public function update(UpdateContactRequest $request, Contact $contact)
    {
        // バリデーション済みのデータを取得
        $validated = $request->validated();

        // タグIDを取り出す
        $tagIds = $validated['tag_ids'] ?? [];

        // お問い合わせ本体を更新
        $contact->update(
            collect($validated)->except('tag_ids')->all()
        );

        // タグの関連付けを更新
        $contact->tags()->sync($tagIds);

        // カテゴリー・タグ情報を読み込む
        $contact->load(['category', 'tags']);

        // 更新したデータをJSON形式で返す
        return new ContactResource($contact);
    }

    // お問い合わせを削除する
    public function destroy(Contact $contact)
    {
        // お問い合わせに紐づくタグの関連付けを解除
        $contact->tags()->detach();

        // お問い合わせを削除
        $contact->delete();

        // 削除成功時は204 No Contentを返す
        return response()->noContent();
    }
}
