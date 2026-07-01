<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class VoleyProTracker
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethod('GET') && !$request->expectsJson()) {
            $ruta = '/' . ltrim($request->path(), '/');

            DB::table('paginas')->updateOrInsert(
                ['ruta' => $ruta],
                [
                    'nombre' => $ruta,
                    'activo' => true,
                ]
            );

            $pagina = DB::table('paginas')->where('ruta', $ruta)->first();

            if ($pagina) {
                DB::table('visitas_pagina')->insert([
                    'pagina_id' => $pagina->id,
                    'usuario_id' => $request->user()?->id,
                    'ip' => $request->ip(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 1000),
                    'fecha' => now(),
                ]);

                if (DB::connection()->getDriverName() !== 'pgsql') {
                    DB::table('paginas')
                        ->where('id', $pagina->id)
                        ->increment('contador_visitas');
                }

                DB::table('bitacora_recursos')->insert([
                    'usuario_id' => $request->user()?->id,
                    'pagina_id' => $pagina->id,
                    'ruta' => $ruta,
                    'metodo' => $request->method(),
                    'accion' => 'Acceso a recurso',
                    'fecha' => now(),
                ]);
            }
        }

        return $response;
    }
}
