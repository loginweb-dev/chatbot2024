<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Contacto extends Model
{
    protected $fillable = ['_serialized', 'number', 'isBusiness', 'isEnterprise', 'name', 'type', 'isMe', 'isUser', 'isGroup', 'isWAContact', 'isMyContact', 'isEnterprise', 'isBlocked', 'bot'];
}
