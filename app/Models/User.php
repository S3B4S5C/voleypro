<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';

    public const CREATED_AT = 'creado_en';
    public const UPDATED_AT = 'actualizado_en';

    protected $fillable = [
        'name',
        'nombre',
        'apellido',
        'ci',
        'telefono',
        'email',
        'password',
        'estado',
        'rol_id',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($user) {
            if (empty($user->estado)) {
                $user->estado = 'Activo';
            }

            if (empty($user->rol_id)) {
                $alumnoRolId = DB::table('roles')->where('nombre', 'Alumno')->value('id');
                $user->rol_id = $alumnoRolId ?: 1;
            }
        });
    }

    public function getNameAttribute(): string
    {
        return trim(($this->nombre ?? '') . ' ' . ($this->apellido ?? ''));
    }

    public function setNameAttribute($value): void
    {
        $parts = explode(' ', trim((string) $value), 2);

        $this->attributes['nombre'] = $parts[0] ?? $value;
        $this->attributes['apellido'] = $parts[1] ?? null;
    }

    public function getRememberToken()
    {
        return null;
    }

    public function setRememberToken($value): void
    {
        //
    }

    public function getRememberTokenName()
    {
        return null;
    }
}
