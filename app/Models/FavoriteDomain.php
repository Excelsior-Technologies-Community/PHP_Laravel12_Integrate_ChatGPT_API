<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FavoriteDomain extends Model
{
    protected $fillable = [
        'topic',
        'domain',
    ];
}