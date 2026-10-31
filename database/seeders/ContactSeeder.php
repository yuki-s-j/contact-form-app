<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Contact::factory()->count(20)->create()->each(function ($contact) {
            $tagIds = Tag::inRandomOrder()->limit(rand(1, 3))->pluck('id');
            $contact->tags()->attach($tagIds);
        });
    }
}
