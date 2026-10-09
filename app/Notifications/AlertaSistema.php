<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AlertaSistema extends Notification
{
    use Queueable;

    public $titulo;
    public $mensaje;
    public $icono;
    public $color;

    // Recibimos los datos de la alerta cuando la creamos
    public function __construct($titulo, $mensaje, $icono = 'fas fa-bell', $color = 'text-primary')
    {
        $this->titulo = $titulo;
        $this->mensaje = $mensaje;
        $this->icono = $icono;
        $this->color = $color;
    }

    // Le decimos a Laravel que guarde esto en la Base de Datos
    public function via($notifiable)
    {
        return ['database'];
    }

    // Estructura de la alerta que se guardará
    public function toDatabase($notifiable)
    {
        return [
            'titulo' => $this->titulo,
            'mensaje' => $this->mensaje,
            'icono' => $this->icono,
            'color' => $this->color,
        ];
    }
}