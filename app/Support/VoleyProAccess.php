<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoleyProAccess
{
    public static function roleName($user): ?string
    {
        if (!$user) {
            return null;
        }

        return DB::table('roles')->where('id', $user->rol_id)->value('nombre');
    }

    public static function isPropietario($user): bool
    {
        return self::roleName($user) === 'Propietario';
    }

    public static function isSecretaria($user): bool
    {
        return self::roleName($user) === 'Secretaria';
    }

    public static function isDocente($user): bool
    {
        return self::roleName($user) === 'Docente';
    }

    public static function isAlumno($user): bool
    {
        return self::roleName($user) === 'Alumno';
    }

    public static function can($user, string $permissionCode, string $action = 'puede_ver'): bool
    {
        if (!$user) {
            return false;
        }

        if (!in_array($action, ['puede_ver', 'puede_crear', 'puede_editar', 'puede_eliminar'], true)) {
            return false;
        }

        return DB::table('matriz_acceso')
            ->join('permisos', 'matriz_acceso.permiso_id', '=', 'permisos.id')
            ->where('matriz_acceso.rol_id', $user->rol_id)
            ->where('permisos.codigo', $permissionCode)
            ->where("matriz_acceso.$action", true)
            ->exists();
    }

    public static function authorize(Request $request, string $permissionCode, string $action = 'puede_ver'): void
    {
        abort_if(!$request->user(), 403, 'Debe iniciar sesión.');

        abort_unless(
            self::can($request->user(), $permissionCode, $action),
            403,
            'No tiene permiso para acceder a este recurso.'
        );
    }

    public static function scopeResource(Builder $query, string $resource, string $table, $user): Builder
    {
        $role = self::roleName($user);

        if (!$user || in_array($role, ['Propietario', 'Secretaria'], true)) {
            return $query;
        }

        if ($resource === 'usuarios') {
            return $query->where("$table.id", $user->id);
        }

        if ($resource === 'ofertas-academicas') {
            if ($role === 'Docente') {
                return $query->where("$table.docente_id", $user->id);
            }

            if ($role === 'Alumno') {
                return $query->where("$table.estado", 'Activa');
            }
        }

        if ($resource === 'horarios') {
            return $query->where("$table.activo", true);
        }

        if ($resource === 'inscripciones') {
            if ($role === 'Alumno') {
                return $query->where("$table.alumno_id", $user->id);
            }

            if ($role === 'Docente') {
                return $query->whereExists(function ($sub) use ($user, $table) {
                    $sub->select(DB::raw(1))
                        ->from('ofertas_academicas')
                        ->whereColumn('ofertas_academicas.id', "$table.oferta_id")
                        ->where('ofertas_academicas.docente_id', $user->id);
                });
            }
        }

        if ($resource === 'seguimientos') {
            if ($role === 'Alumno') {
                return $query->whereExists(function ($sub) use ($user, $table) {
                    $sub->select(DB::raw(1))
                        ->from('inscripciones')
                        ->whereColumn('inscripciones.id', "$table.inscripcion_id")
                        ->where('inscripciones.alumno_id', $user->id);
                });
            }

            if ($role === 'Docente') {
                return $query->whereExists(function ($sub) use ($user, $table) {
                    $sub->select(DB::raw(1))
                        ->from('inscripciones')
                        ->join('ofertas_academicas', 'inscripciones.oferta_id', '=', 'ofertas_academicas.id')
                        ->whereColumn('inscripciones.id', "$table.inscripcion_id")
                        ->where('ofertas_academicas.docente_id', $user->id);
                });
            }
        }

        if ($resource === 'planes-pago') {
            if ($role === 'Alumno') {
                return $query->whereExists(function ($sub) use ($user, $table) {
                    $sub->select(DB::raw(1))
                        ->from('inscripciones')
                        ->whereColumn('inscripciones.id', "$table.inscripcion_id")
                        ->where('inscripciones.alumno_id', $user->id);
                });
            }
        }

        if ($resource === 'cuotas-pago') {
            if ($role === 'Alumno') {
                return $query->whereExists(function ($sub) use ($user, $table) {
                    $sub->select(DB::raw(1))
                        ->from('planes_pago')
                        ->join('inscripciones', 'planes_pago.inscripcion_id', '=', 'inscripciones.id')
                        ->whereColumn('planes_pago.id', "$table.plan_pago_id")
                        ->where('inscripciones.alumno_id', $user->id);
                });
            }
        }

        if ($resource === 'pagos') {
            if ($role === 'Alumno') {
                return $query->where("$table.usuario_id", $user->id);
            }
        }

        return $query;
    }

    public static function resourceFromTable(string $table): ?string
    {
        return match ($table) {
            'usuarios' => 'usuarios',
            'aulas_canchas' => 'aulas-canchas',
            'horarios' => 'horarios',
            'ofertas_academicas' => 'ofertas-academicas',
            'inscripciones' => 'inscripciones',
            'seguimientos' => 'seguimientos',
            'planes_pago' => 'planes-pago',
            'cuotas_pago' => 'cuotas-pago',
            'pagos' => 'pagos',
            default => null,
        };
    }

    public static function userOwnsPayment($user, int $pagoId): bool
    {
        if (!$user) {
            return false;
        }

        if (in_array(self::roleName($user), ['Propietario', 'Secretaria'], true)) {
            return true;
        }

        return DB::table('pagos')
            ->where('id', $pagoId)
            ->where('usuario_id', $user->id)
            ->exists();
    }
}
