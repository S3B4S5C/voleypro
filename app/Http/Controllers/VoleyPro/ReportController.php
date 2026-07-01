<?php

namespace App\Http\Controllers\VoleyPro;

use App\Http\Controllers\Controller;
use App\Support\VoleyProAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $desde = $request->get('desde');
        $hasta = $request->get('hasta');

        $pagos = DB::table('pagos');
        VoleyProAccess::scopeResource($pagos, 'pagos', 'pagos', $request->user());
        $this->dateFilter($pagos, 'fecha', $desde, $hasta);

        $inscripciones = DB::table('inscripciones');
        VoleyProAccess::scopeResource($inscripciones, 'inscripciones', 'inscripciones', $request->user());

        return Inertia::render('VoleyPro/Reports', [
            'filters' => [
                'desde' => $desde,
                'hasta' => $hasta,
            ],

            'pagosPorEstado' => $pagos
                ->select('estado', DB::raw('COUNT(*) as total'), DB::raw('SUM(monto) as monto'))
                ->groupBy('estado')
                ->get(),

            'alumnosPorEstado' => DB::table('usuarios')
                ->join('roles', 'usuarios.rol_id', '=', 'roles.id')
                ->where('roles.nombre', 'Alumno')
                ->select('usuarios.estado', DB::raw('COUNT(*) as total'))
                ->groupBy('usuarios.estado')
                ->get(),

            'inscripcionesPorEstado' => $inscripciones
                ->select('estado', DB::raw('COUNT(*) as total'))
                ->groupBy('estado')
                ->get(),

            'recursosMasAccedidos' => DB::table('bitacora_recursos')
                ->select('ruta', DB::raw('COUNT(*) as total'))
                ->groupBy('ruta')
                ->orderByDesc('total')
                ->limit(10)
                ->get(),

            'busquedasFrecuentes' => DB::table('busquedas')
                ->select('termino', DB::raw('COUNT(*) as total'))
                ->groupBy('termino')
                ->orderByDesc('total')
                ->limit(10)
                ->get(),
        ]);
    }

    public function exportar(Request $request): Response
    {
        $tipo = $request->get('tipo', 'pagos');
        $desde = $request->get('desde');
        $hasta = $request->get('hasta');

        if ($tipo === 'accesos') {
            $rows = DB::table('bitacora_recursos')
                ->select('ruta', 'metodo', 'accion', 'fecha')
                ->orderByDesc('id')
                ->limit(1000)
                ->get();

            return $this->csv('reporte_accesos.csv', ['Ruta', 'Método', 'Acción', 'Fecha'], $rows, fn ($r) => [
                $r->ruta,
                $r->metodo,
                $r->accion,
                $r->fecha,
            ]);
        }

        $query = DB::table('pagos')
            ->select('id', 'monto', 'estado', 'payment_number', 'pagofacil_status', 'fecha');

        VoleyProAccess::scopeResource($query, 'pagos', 'pagos', $request->user());
        $this->dateFilter($query, 'fecha', $desde, $hasta);

        $rows = $query->orderByDesc('id')->limit(1000)->get();

        return $this->csv('reporte_pagos.csv', ['ID', 'Monto', 'Estado', 'Payment Number', 'PagoFácil', 'Fecha'], $rows, fn ($r) => [
            $r->id,
            $r->monto,
            $r->estado,
            $r->payment_number,
            $r->pagofacil_status,
            $r->fecha,
        ]);
    }

    private function dateFilter($query, string $column, ?string $desde, ?string $hasta): void
    {
        if ($desde) {
            $query->whereDate($column, '>=', $desde);
        }

        if ($hasta) {
            $query->whereDate($column, '<=', $hasta);
        }
    }

    private function csv(string $filename, array $headers, $rows, callable $map): Response
    {
        $content = implode(';', $headers) . PHP_EOL;

        foreach ($rows as $row) {
            $content .= implode(';', array_map(function ($value) {
                    $value = str_replace(["\n", "\r", ';'], [' ', ' ', ','], (string) $value);
                    return '"' . $value . '"';
                }, $map($row))) . PHP_EOL;
        }

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
