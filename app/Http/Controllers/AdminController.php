<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Contact::with(['category', 'tags']);

        // 名前・メールアドレスの検索
        if ($request->filled('keyword')) {
            $keyword = $request->input('keyword');

            $query->where(function ($q) use ($keyword) {
                $q->where('first_name', 'like', "%{$keyword}%")->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$keyword}%"])->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        // 性別の検索
        if ($request->filled('gender') && $request->input('gender') !== '0') {
            $query->where('gender', $request->input('gender'));
        }

        // カテゴリーの検索
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // 日付の検索
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        $contacts = $query->latest()->paginate(7);

        $categories = Category::all();
        $tags = Tag::all();

        return view('admin.index', compact('contacts', 'categories', 'tags'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact)
    {
        $contact->load(['category', 'tags']);

        return view('admin.show', compact('contact'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Contact $contact)
    {
        $contact->delete();

        return redirect('/admin');
    }
}
