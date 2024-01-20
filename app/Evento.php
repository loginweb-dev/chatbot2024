<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Cliente;
use App\Grupo;
use App\Whatsapp;

class Evento extends Model
{
    protected $fillable = ['datos', 'tipo', 'mensaje', 'bot', 'desde', 'file', 'extension', 'subtipo', 'author', 'subtype', 'clase', 'whatsapp', 'user_id'];

    
	protected $appends=['published', 'fserver', 'fwhats'];
	public function getPublishedAttribute(){
		return Carbon::createFromTimeStamp(strtotime($this->attributes['created_at']) )->diffForHumans();
	}
	public function getFserverAttribute(){
		return date('Y-m-d H:i:s', strtotime($this->attributes['created_at']));
	}
	public function getFwhatsAttribute(){
		return date("Y-m-d H:i:s", $this->attributes['whatsapp']);
	}

	// public function getContactoAttribute(){
	// 	return $this->attributes['number'].'@c.us';
	// }

	public function contacto()
    {
        return $this->belongsTo(Contacto::class, 'desde', 'codigo');
    }
	public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'desde', 'codigo');
    }
	public function miauthor()
    {
        return $this->belongsTo(Contacto::class, 'author', 'codigo');
    }
	public function miwhats()
    {
        return $this->belongsTo(Whatsapp::class, 'bot', 'codigo');
    }
}
