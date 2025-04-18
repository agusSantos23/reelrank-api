<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovieGenre extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $primaryKey = ['movie_id', 'genre_id'];

    protected $fillable = [
        'movie_id',
        'genre_id',
    ];

    protected $hidden = [
        'movie_id',
        'genre_id',
    ];
}
