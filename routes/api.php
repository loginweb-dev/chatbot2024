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
use App\Descarga;
use App\Plantilla;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
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
	// return $request->avatar;
	try {		
		$mifind = Contacto::where('number', $request->number)->where('bot', $request->bot)->first();
		if ($mifind) {
			Storage::disk('public')->delete($mifind->avatar);
			$mifind->name = $request->midata["name"];
			$mifind->shortName = $request->midata["shortName"];
			$mifind->isBusiness = $request->midata["isBusiness"];
			$mifind->isBlocked = $request->midata["isBlocked"];
			$mifind->avatar = $request->avatar;
			$mifind->send = false;
			$mifind->user_id = $request->user_id;
			$mifind->save();			
		}else{
			$mifind = Contacto::create($request->midata);
			$mifind->bot = $request->bot;
			$mifind->_id = $request->_id;
			$mifind->avatar = $request->avatar;
			$mifind->send = false;
			$mifind->user_id = $request->user_id;
			$mifind->codigo = $request->midata["id"]["_serialized"];
			$mifind->save();
		}
		event(new MiEvent([
			'mensaje'=> "Se agrego o actualizo el contacto ".$request->midata["name"]. ", con el codigo  ".$request->number,
			'bot' => $request->bot,
			'tipo' => $request->tipo,
			'file' => $request->avatar ? $request->avatar : null,
			'fwhats' => date('Y-m-d H:i:s')
		]));
		return $mifind;
    } catch (Exception $e) {
		event(new MiEvent([
			'error' => 'contacto'
		]));
    	return $e;
	}
});

Route::post('/socket/contacto/update', function (Request $request) {
	// $midata = Contacto::where('codigo', $request->codigo)->where('bot', $request->bot)->first();
	// $midata->send = true;
	// $midata->save();
	// sleep($request->segundos);
	return true;
});

Route::post('/socket/grupos', function (Request $request) {
	try {		
		$mifind = Grupo::where('bot', $request->bot)->where('codigo', $request->codigo)->first();
		if ($mifind) {
			$mifind->name = $request->name;
			$mifind->_id = json_encode($request->_id);
			$mifind->desc = $request->desc;
			$mifind->groupMetadata = json_encode($request->groupMetadata);
			$mifind->lastMessage = json_encode($request->lastMessage);
			$mifind->owner = json_encode($request->owner);
			$mifind->send = false;
			$mifind->user_id = $request->user_id;
			$mifind->save();
		}else{
			$mifind = Grupo::create([
				'_id' => json_encode($request->_id),
				'name' => $request->name,
				'bot' => $request->bot,
				'codigo' => $request->codigo,
				'isReadOnly' => $request->isReadOnly,
				'isMuted' => $request->isMuted,
				'groupMetadata' => json_encode($request->groupMetadata),
				'lastMessage' => json_encode($request->lastMessage),
				'owner' => json_encode($request->owner),
				'creation' => $request->creation,
				'desc' => $request->desc,
				'send' => false,
				'user_id' => $request->user_id
			]);
		}
		event(new MiEvent([
			'mensaje'=> "Se agrego o actualizo el grupo ".$request->name.", con el codigo  ".$request->codigo,
			'bot' => $request->bot,
			'fwhats' => date('Y-m-d H:i:s'),
			'tipo' => $request->tipo
		]));
		return $mifind;
	} catch (Exception $e) {
		event(new MiEvent([
			'error' => 'grupo'
		]));
    	return $e;
	}

});

Route::post('/socket/grupo/update', function (Request $request) {
	// $midata = Grupo::where('codigo', $request->codigo)->where('slug', $request->bot)->first();
	// $midata->send = true;
	// $midata->save();
	// // sleep($request->segundos);
	// return true;
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
			$mievent =  Evento::where('id', $mienveto->id)->with('contacto', 'grupo', 'miauthor')->first();
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
			'error' => $e
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
	// ->where('created_at', '>=', date('Y-m-d'))
	->orderBy('created_at', 'desc')
	->with('contacto', 'grupo', 'miauthor')
	->take(15)
	->get();
});

Route::post('/whatsapp/chats', function (Request $request) {
	return Evento::where('bot', $request->codigo)
	->where('created_at', '>=', date('Y-m-d'))
	->orderBy('created_at', 'desc')
	->with('contacto', 'grupo', 'miauthor')
	->get();
});

Route::post('/whatsapp/estados', function (Request $request) {
	return Evento::where("bot", $request->codigo)
		->where('created_at', '>=', date('Y-m-d'))
		->where('subtipo', 'status')
		->orderBy('created_at', 'desc')
		->with('contacto', 'grupo', 'miauthor')
		->get();
});

Route::post('/whatsapp/grupo', function (Request $request) {
	return Evento::where("bot", $request->whatsapp)
		->where('created_at', '>=', date('Y-m-d'))
		->where('tipo', 'chat_group')
		->orderBy('created_at', 'desc')
		->with('contacto', 'grupo', 'miauthor')
		->get();
});

Route::post('/whatsapp/grupo2', function (Request $request) {
	return Evento::where("bot", $request->whatsapp)
		->where('created_at', '>=', date('Y-m-d'))
		->where('tipo', 'chat_multimedia')
		->where('subtipo', 'chat_group')
		->orderBy('created_at', 'desc')
		->with('contacto', 'grupo', 'miauthor')
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


/// download ---------------------

Route::post('/socket/download/update', function (Request $request) {
	$midata = Descarga::where('slug', $request->slug)->first();
	$midata->file = $request->file;
	$midata->send = $request->send;
	$midata->size = $request->size;
	$midata->save();
	event(new MiEvent([
		'tipo' => 'download'
	]));
	return true;
});

//template------------------------------
Route::post('/socket/template/update', function (Request $request) {
	$midata = Plantilla::find($request->id);
	$midata->send = $request->send;
	$midata->size = $request->size;
	$midata->save();
	event(new MiEvent([
		'tipo' => 'template'
	]));
	return true;
});