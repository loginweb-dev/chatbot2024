<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Descarga extends Model
{
    protected $fillable = ['slug', 'size', 'send', 'file', 'user_id'];

}
