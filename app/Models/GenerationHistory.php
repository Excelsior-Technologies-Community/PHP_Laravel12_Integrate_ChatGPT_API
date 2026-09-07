<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GenerationHistory extends Model
{
    protected $fillable = [
        'topic',
        'result',
    ];
}