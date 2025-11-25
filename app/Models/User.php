<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Entrada; // <-- Importar Entrada
use App\Models\Cliente; // <-- Importar Cliente
use App\Models\Pedido;  // <-- Importar Pedido

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'activo'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function entradas(){
        return $this->hasMany(Entrada::class);
    }

    
    public function clientes(){
        return $this->hasMany(Cliente::class);
    }

    // <-- ¡NUEVA RELACIÓN!
    /**
     * Un Agente (User) tiene muchos Pedidos.
     */
    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }
}