<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Grupo extends Model
{
    protected $fillable = ['_id', 'codigo', 'bot', 'name', 'isReadOnly', 'isMuted', 'groupMetadata', 'lastMessage', 'owner', 'desc', 'creation'];
}
