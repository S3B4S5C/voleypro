<?php

namespace App\Http\Controllers\VoleyPro;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SecurityController extends Controller
{
    public function index()
    {
        return Inertia::render('VoleyPro/Security', [
            'roles' => DB::table('roles')->orderBy('nombre')->get(),

            'permisos' => DB::table('permisos')
                ->orderBy('modulo')
                ->orderBy('nombre')
                ->get(),

            'matriz' => DB::table('matriz_acceso')
                ->join('roles', 'matriz_acceso.rol_id', '=', 'roles.id')
                ->join('permisos', 'matriz_acceso.permiso_id', '=', 'permisos.id')
                ->select(
                    'roles.nombre as rol',
                    'permisos.nombre as permiso',
                    'permisos.codigo',
                    'matriz_acceso.puede_ver',
                    'matriz_acceso.puede_crear',
                    'matriz_acceso.puede_editar',
                    'matriz_acceso.puede_eliminar'
                )
                ->orderBy('roles.nombre')
                ->orderBy('permisos.nombre')
                ->get(),
        ]);
    }
}
