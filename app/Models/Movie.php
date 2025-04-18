<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Movie extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'movies';

    protected $fillable = [
        'id',
        'title',
        'original_title',
        'overview',
        'original_language',
        'score',
        'release_date',
        'budget',
        'revenue',
        'runtime',
        'status',
        'poster_id',
        'backdrop_id',
    ];

    protected $casts = [
        'release_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid();
            }
        });
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'movie_genres', 'movie_id', 'genre_id');
    }
}
