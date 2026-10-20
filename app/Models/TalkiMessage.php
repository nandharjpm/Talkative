<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TalkiMessage extends Model
{
    protected $fillable = [
        'user_id',
        'role',
        'content',
    ];
}
