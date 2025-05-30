<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Avatar extends Model
{
  use HasFactory;

  protected $table = 'avatars';

  public $incrementing = false;

  protected $keyType = 'string';


  protected $fillable = [
    'saga_id',
    'image_id',
  ];

  public function saga(): BelongsTo
  {
    return $this->belongsTo(Saga::class, 'saga_id');
  }

}
