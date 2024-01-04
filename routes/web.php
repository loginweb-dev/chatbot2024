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
    // return view('welcome');
	return redirect('/admin');
});



// Route::get('/users', [App\Http\Controllers\UserController::class, 'index'])->name('users.index');

Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
    Route::get('/clear', function () {
        // return asset('storage/mi-whatsapp-01');
        App\Evento::truncate();
        App\Contacto::truncate();
        App\Grupo::truncate();
        Storage::disk('public')->deleteDirectory('mi-whatsapp-01');
        Storage::disk('public')->deleteDirectory('qr');
        return redirect('/admin/whatsapps');
    });
});

// Auth::routes();
// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

