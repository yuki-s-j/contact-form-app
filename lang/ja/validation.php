<?php

return [
    'required' => ':attributeは必須です。',
    'string' => ':attributeは文字列で入力してください。',
    'integer' => ':attributeは整数で入力してください。',
    'email' => ':attributeは正しいメールアドレス形式で入力してください。',
    'max' => [
        'string' => ':attributeは:max文字以内で入力してください。',
        'numeric' => ':attributeは:max以下で入力してください。',
    ],
    'min' => [
        'numeric' => ':attributeは:min以上で入力してください。',
    ],
    'in' => ':attributeの選択値が正しくありません。',
    'exists' => '選択された:attributeは存在しません。',
    'regex' => ':attributeの形式が正しくありません。',
    'array' => ':attributeは配列で指定してください。',
    'date_format' => ':attributeは:format形式で入力してください。',

    'attributes' => [
        'first_name' => '名',
        'last_name' => '姓',
        'gender' => '性別',
        'email' => 'メールアドレス',
        'tel' => '電話番号',
        'address' => '住所',
        'building' => '建物名',
        'category_id' => 'お問い合わせの種類',
        'detail' => 'お問い合わせ内容',
        'tag_ids' => 'タグ',
        'keyword' => 'キーワード',
        'date' => '日付',
        'page' => 'ページ',
        'per_page' => '表示件数',
    ],
];