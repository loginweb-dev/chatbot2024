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
        App\Evento::where('user_id', Auth::user()->id)->truncate();
        App\Contacto::where('user_id', Auth::user()->id)->truncate();
        App\Grupo::where('user_id', Auth::user()->id)->truncate();       
        $miwhats = App\Whatsapp::where('user_id', Auth::user()->id)->get();
        foreach ($miwhats as $value) {
            Storage::disk('public')->deleteDirectory($value->slug);
        }        
        // Storage::disk('public')->deleteDirectory('qr');

        // App\Whatsapp::where('user_id', Auth::user()->id)->truncate();
        // App\Descarga::where('user_id', Auth::user()->id)->truncate();
        // App\Plantilla::truncate();
        // $miuser = App\Models\User::all();
        // foreach ($miuser as $value) {
        //     Storage::disk('public')->deleteDirectory($value->name);
        // }   

        // App\Subscription::truncate(); 
        // App\Product::truncate();  
        // Storage::disk('public')->deleteDirectory('plantillas');
        return redirect('/admin/whatsapps');
    });
});
