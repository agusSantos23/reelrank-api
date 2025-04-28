<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMovie extends Model
{
  protected $table = 'user_movies';

  protected $primaryKey = ['user_id', 'movie_id'];

  public $incrementing = false;

  protected $fillable = [
    'user_id',
    'movie_id',
    'is_favorite',
    'seen',
    'rating',
    'story_rating',
    'acting_rating',
    'visuals_rating',
    'music_rating',
    'entertainment_rating',
    'pacing_rating',
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function movie(): BelongsTo
  {
    return $this->belongsTo(Movie::class);
  }
}
