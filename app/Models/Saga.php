<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Saga extends Model
{
  use HasFactory;

  protected $table = 'sagas';

  protected $fillable = [
    'name',
  ];
  protected $casts = [
    'id' => 'string',
  ];

  public function avatars(): HasMany
  {
    return $this->hasMany(Avatar::class);
  }
}
