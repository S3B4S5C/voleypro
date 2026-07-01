<?php

namespace App\Http\Controllers\VoleyPro;

use App\Http\Controllers\Controller;
use App\Support\VoleyProAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $term = trim((string) $request->get('q', ''));

        $results = [
            'usuarios' => [],
            'ofertas_academicas' => [],
            'inscripciones' => [],
            'pagos' => [],
        ];

        if ($term !== '') {
            $like = '%' . $term . '%';

            $usuarios = DB::table('usuarios');
            VoleyProAccess::scopeResource($usuarios, 'usuarios', 'usuarios', $request->user());

            $results['usuarios'] = $usuarios
                ->where(function ($q) use ($like) {
                    $q->where('nombre', 'like', $like)
                        ->orWhere('apellido', 'like', $like)
                        ->orWhere('ci', 'like', $like)
                        ->orWhere('telefono', 'like', $like)
                        ->orWhere('email', 'like', $like);
                })
                ->limit(10)
                ->get();

            $ofertas = DB::table('ofertas_academicas');
            VoleyProAccess::scopeResource($ofertas, 'ofertas-academicas', 'ofertas_academicas', $request->user());

            $results['ofertas_academicas'] = $ofertas
                ->where(function ($q) use ($like) {
                    $q->where('nombre', 'like', $like)
                        ->orWhere('gestion', 'like', $like)
                        ->orWhere('estado', 'like', $like);
                })
                ->limit(10)
                ->get();

            $inscripciones = DB::table('inscripciones');
            VoleyProAccess::scopeResource($inscripciones, 'inscripciones', 'inscripciones', $request->user());

            $results['inscripciones'] = $inscripciones
                ->where('estado', 'like', $like)
                ->limit(10)
                ->get();

            $pagos = DB::table('pagos');
            VoleyProAccess::scopeResource($pagos, 'pagos', 'pagos', $request->user());

            $results['pagos'] = $pagos
                ->where(function ($q) use ($like) {
                    $q->where('payment_number', 'like', $like)
                        ->orWhere('estado', 'like', $like)
                        ->orWhere('pagofacil_status', 'like', $like);
                })
                ->limit(10)
                ->get();

            $total = collect($results)->sum(fn ($rows) => count($rows));

            DB::table('busquedas')->insert([
                'usuario_id' => $request->user()?->id,
                'termino' => $term,
                'modulo' => 'General',
                'resultados' => $total,
                'fecha' => now(),
            ]);
        }

        return Inertia::render('VoleyPro/Search', [
            'q' => $term,
            'results' => $results,
        ]);
    }
}
