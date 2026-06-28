<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class News extends Model
{
    use HasFactory;

    /**
     * The database table used by the model.
     *
     * Eloquent would otherwise pluralise "News" to "news" already, but we set
     * it explicitly for clarity.
     */
    protected $table = 'news';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'image_url',
        'author',
        'published_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    /**
     * Use the slug for implicit route-model binding.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Build a URL-safe slug from a title that is guaranteed unique against the
     * `news` table, appending `-2`, `-3`, … on collision (mirrors the seeder).
     */
    public static function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);

        // Fall back to a random token when the title has no slug-able characters
        // (e.g. a purely Cyrillic title can transliterate to an empty string).
        if ($base === '') {
            $base = Str::lower(Str::random(8));
        }

        $slug = $base;
        $suffix = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
