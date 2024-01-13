<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Contacto extends Model
{
    protected $fillable = ['_id', 'number', 'isBusiness', 'isEnterprise', 'name', 'type', 'isMe', 'isUser', 'isGroup', 'isWAContact', 'isMyContact', 'isEnterprise', 'isBlocked', 'bot', 'avatar', 'codigo', 'send', 'class', 'user_id'];
}
