<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
	return redirect('/admin');
});

Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
    Route::get('/clear', function () {

        App\Evento::truncate();
        App\Contacto::truncate();
        App\Grupo::truncate();
        $miwhats = App\Whatsapp::all();
        foreach ($miwhats as $value) {
            Storage::disk('public')->deleteDirectory($value->slug);
        }        
        Storage::disk('public')->deleteDirectory('qr');
        return redirect('/admin/whatsapps');
    });
});
