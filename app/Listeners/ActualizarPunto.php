<?php

namespace App\Listeners;

use App\Events\RegistroPunto;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\RegistroPunto;
class ActualizarPunto
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\RegistroPunto  $event
     * @return void
     */
    public function handle(RegistroPunto $event)
    {
        //
    	info('nuevo punto registrado..'. $event->punto);
    }
}
