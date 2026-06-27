<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
