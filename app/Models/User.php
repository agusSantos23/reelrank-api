<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as AuthenticatableUser; 
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends AuthenticatableUser implements JWTSubject
{
    use HasFactory;

    protected $table = 'users';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'lastname',
        'email',
        'password',
        'config_scorer',
        'maximum_star_rating',
        'maximum_slider_rating',
        'avatar_id',
        'status',
        'action_count'
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
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

    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class, 'user_movies');
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'user_genres');
    }

    public function avatar(): BelongsTo
    {
        return $this->belongsTo(Avatar::class, 'avatar_id');
    }

    public function ratedMovies(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class, 'user_movies')->wherePivotNotNull('rating')->withPivot('rating');
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'email' => $this->email,
            'id' => $this->id,
        ];
    }
}
