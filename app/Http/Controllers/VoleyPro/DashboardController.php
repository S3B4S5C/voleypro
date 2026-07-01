<?php

namespace App\Http\Controllers\VoleyPro;

use App\Http\Controllers\Controller;
use App\Support\VoleyProAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = VoleyProAccess::roleName($user);

        $pagosQuery = DB::table('pagos');
        VoleyProAccess::scopeResource($pagosQuery, 'pagos', 'pagos', $user);

        $inscripcionesQuery = DB::table('inscripciones');
        VoleyProAccess::scopeResource($inscripcionesQuery, 'inscripciones', 'inscripciones', $user);

        $ofertasQuery = DB::table('ofertas_academicas');
        VoleyProAccess::scopeResource($ofertasQuery, 'ofertas-academicas', 'ofertas_academicas', $user);

        return Inertia::render('VoleyPro/Dashboard', [
            'stats' => [
                'alumnos' => in_array($role, ['Propietario', 'Secretaria'], true)
                    ? DB::table('usuarios')->join('roles', 'usuarios.rol_id', '=', 'roles.id')->where('roles.nombre', 'Alumno')->count()
                    : ($role === 'Alumno' ? 1 : 0),

                'entrenadores' => in_array($role, ['Propietario', 'Secretaria'], true)
                    ? DB::table('usuarios')->join('roles', 'usuarios.rol_id', '=', 'roles.id')->where('roles.nombre', 'Docente')->count()
                    : ($role === 'Docente' ? 1 : 0),

                'grupos' => $ofertasQuery->count(),

                'inscripciones' => (clone $inscripcionesQuery)->where('estado', 'Activa')->count(),

                'pagosPendientes' => (clone $pagosQuery)->where('estado', 'Pendiente')->count(),

                'pagosConfirmados' => (clone $pagosQuery)->where('estado', 'Pagado')->count(),

                'totalCobrado' => (clone $pagosQuery)->where('estado', 'Pagado')->sum('monto'),

                'visitas' => DB::table('paginas')->sum('contador_visitas'),
            ],

            'ultimosPagos' => (clone $pagosQuery)
                ->orderByDesc('id')
                ->limit(5)
                ->get(),

            'paginasMasVisitadas' => DB::table('paginas')
                ->orderByDesc('contador_visitas')
                ->limit(5)
                ->get(),
        ]);
    }
}
