<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Event;
use App\Events\MiEvent;
use App\Cliente;
use App\Grupo;
use App\Evento;
use App\Whatsapp;
use App\Contacto;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/socket/contactos', function (Request $request) {
	try {		
		// return $request->bot;
		// $midata =  json_decode(stripslashes($request->all()), true);
		$midata = json_decode($request->datos, true);
		// return $midata;
		// foreach ($variable as $key => $value) {
		// 	# code...
		// }
		// foreach ($midata as $key => $value) {
			# code...
		// return  $request->datos;
		// for ($i=0; $i < ; $i++) { 
foreach ($midata as $value) {
	# code...
	// $miaux = json_encode($value, true);
	return $value;
}
		// 	$miitem =  json_encode($value);
		// 	return $value->number;
		// 	$micliente = Contacto::where('number', $value->number)->where('bot', $request->bot)->first();
		// 	if(!$micliente){
		// 		$micliente = Cliente::create([
		// 			'nombre'=> $request->nombre,
		// 			'whatsapp' => $request->from,
		// 			'tipo' => 'Nuevo',
		// 			'bot' => $request->bot
		// 		]);
		// 	}	
		// }
		return true;
    } catch (Exception $e) {
		event(new MiEvent([
			'error' => 'cliente'
		]));
    	return $e;
	}
});

Route::post('/socket/grupo', function (Request $request) {
	try {		
		$migrupo = Grupo::where('codigo', $request->from)->first();
		if(!$migrupo){
			$migrupo = Grupo::create([
				'nombre'=> $request->nombre,
				'codigo' => $request->from,
				'bot' => $request->bot
			]);
		}		
		return true;
	} catch (Exception $e) {
		event(new MiEvent([
			'error' => 'grupo'
		]));
    	return $e;
	}
});

Route::post('/socket/evento', function (Request $request) {	
	try {
		if (($request->tipo != 'qr') && ($request->tipo != 'ready') && ($request->tipo != 'authenticated') && ($request->tipo != 'init') && ($request->tipo != 'status') && ($request->tipo != 'destroy')) {
			$mienveto = Evento::create([
				'clase' => $request->clase,
				'mensaje'=> $request->mensaje,
				'tipo' => $request->tipo,
				'datos' => json_encode($request->datos),
				'bot' => $request->bot,
				'desde' => $request->desde,
				'file' => $request->file,
				'extension' => $request->extension,
				'subtipo' => $request->subtipo,
				'author' => $request->author,
				'subtype' => $request->subtype,
				'whatsapp'=> $request->whatsapp
			]);		
			$mievent =  Evento::where('id', $mienveto->id)->with('cliente', 'grupo', 'miauthor')->first();
			event(new MiEvent($mievent));
		}else{
			event(new MiEvent([
				'clase' => $request->clase,
				'bot' => $request->bot,
				'desde' => $request->desde,
				'mensaje' => $request->mensaje,
				'fwhats' => date('Y-m-d H:i:s'),
				'tipo' => $request->tipo,
				'file' => $request->file,
				'extension' => $request->extension,
				'subtipo' => $request->subtipo,
				'author' => $request->author,
				'subtype' => $request->subtype,
				'datos' => json_encode($request->datos),
				'whatsapp'=> $request->whatsapp
			]));		
		}
		
		return true;
	} catch (Exception $e) {
		event(new MiEvent([
			'error' => 'evento'
		]));
		return $e;
	}
});

Route::post('/socket/reset', function (Request $request) {
	$miwhats = Whatsapp::query()->update([
		'estado' => false
	]);
	return true;
});

Route::post('/socket/qr', function (Request $request) {
	event(new MiEvent($request->all()));
	$miwhats = Whatsapp::where('codigo', $request->whatsapp)->first();
	return true;
});

Route::post('/socket/estado', function (Request $request) {
	// event(new MiEvent($request->all()));
	$miwhats = Whatsapp::where('codigo', $request->bot)->first();
	if ($miwhats) {
		$miwhats->estado = $request->estado;
		$miwhats->save();
	}
	return true;
});

Route::post('/socket/chats', function (Request $request) {
	event(new MiEvent($request->all()));
	$miwhats = Whatsapp::where('codigo', $request->whatsapp)->first();
	if ($miwhats) {
		$miwhats->chats = json_encode($request->chats);
		$miwhats->save();
	}
	return true;
});

Route::post('/socket/contacts', function (Request $request) {
	event(new MiEvent($request->all()));
	$miwhats = Whatsapp::where('codigo', $request->whatsapp)->first();
	if ($miwhats) {
		$miwhats->contactos = json_encode($request->contacts);
		$miwhats->save();
	}
	return true;
});




Route::post('/cliente/find', function (Request $request) {
	return Cliente::where("whatsapp", $request->whatsapp)->first();
});

Route::post('/grupo/find', function (Request $request) {
	return Grupo::where("codigo", $request->codigo)->first();
});

Route::post('/whatsapp/listar', function (Request $request) {
	return Evento::where('bot', $request->bot)
	->where('created_at', '>=', date('Y-m-d'))
	->orderBy('created_at', 'desc')
	// ->with('cliente', 'grupo', 'miauthor')
	->take(15)
	->get();
});

Route::post('/whatsapp/chats', function (Request $request) {
	return Evento::where('bot', $request->codigo)
	->where('created_at', '>=', date('Y-m-d'))
	->orderBy('created_at', 'desc')
	->with('cliente', 'grupo', 'miauthor')
	->get();
});

Route::post('/whatsapp/estados', function (Request $request) {
	return Evento::where("bot", $request->whasapp)
		->where('created_at', '>=', date('Y-m-d'))
		->where('desde', 'status@broadcast')
		->orderBy('created_at', 'desc')
		->with('cliente', 'grupo', 'miauthor')
		->get();
});

Route::post('/whatsapp/grupo', function (Request $request) {
	return Evento::where("bot", $request->whatsapp)
		->where('created_at', '>=', date('Y-m-d'))
		->where('tipo', 'chat_group')
		->orderBy('created_at', 'desc')
		->with('cliente', 'grupo', 'miauthor')
		->get();
});

Route::post('/whatsapp/grupo2', function (Request $request) {
	return Evento::where("bot", $request->whatsapp)
		->where('created_at', '>=', date('Y-m-d'))
		->where('tipo', 'chat_multimedia')
		->where('subtipo', 'chat_group')
		->orderBy('created_at', 'desc')
		->with('cliente', 'grupo', 'miauthor')
		->get();
});


Route::post('/ai/mistral', function (Request $request) {
	$data1 = [
		'model' => 'mistral',
		'prompt' => 'hola, quien eres ?',
		'stream' => false,
		'format' => 'json'
	];
	
	$curl = curl_init();
	
	curl_setopt_array($curl, array(
		CURLOPT_URL => "http://localhost:11434/api/generate",// your preferred url
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30000,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_POSTFIELDS => json_encode($data1),
		CURLOPT_HTTPHEADER => array(
			// Set here requred headers
			"accept: */*",
			"accept-language: en-US,en;q=0.8",
			"content-type: application/json",
		),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	curl_close($curl);

	if ($err) {
		return  "cURL Error #:" . $err;
	} else {
		return json_decode($response);
	}
});