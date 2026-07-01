<?php

namespace App\Providers;

use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(Login::class, function (Login $event) {
            if (!Schema::hasTable('bitacora_accesos')) {
                return;
            }

            DB::table('bitacora_accesos')->insert([
                'usuario_id' => $event->user->id,
                'email_intento' => $event->user->email,
                'tipo' => 'LOGIN_OK',
                'ip' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 1000),
                'descripcion' => 'Inicio de sesión aceptado.',
                'fecha' => now(),
            ]);
        });

        Event::listen(Failed::class, function (Failed $event) {
            if (!Schema::hasTable('bitacora_accesos')) {
                return;
            }

            DB::table('bitacora_accesos')->insert([
                'usuario_id' => null,
                'email_intento' => $event->credentials['email'] ?? null,
                'tipo' => 'LOGIN_FALLIDO',
                'ip' => request()->ip(),
                'user_agent' => substr((string) request()->userAgent(), 0, 1000),
                'descripcion' => 'Intento de inicio de sesión fallido.',
                'fecha' => now(),
            ]);
        });

        Inertia::share('flash', function () {
            return [
                'success' => session('success'),
                'error' => session('error'),
            ];
        });

        Inertia::share('voleypro', function (Request $request) {
            $user = $request->user();

            if (!$user || !Schema::hasTable('menus')) {
                return [
                    'menu' => [],
                    'theme' => [
                        'class' => 'theme-youth',
                        'fontSize' => 'normal',
                        'contrast' => false,
                        'auto' => true,
                    ],
                    'pageVisits' => 0,
                ];
            }

            $ruta = '/' . ltrim($request->path(), '/');

            return [
                'menu' => $this->menuForUser($user),
                'theme' => $this->themeForUser($user),
                'pageVisits' => Schema::hasTable('paginas')
                    ? (DB::table('paginas')->where('ruta', $ruta)->value('contador_visitas') ?? 0)
                    : 0,
            ];
        });
    }

    private function menuForUser($user)
    {
        return DB::table('menus')
            ->join('menu_por_rol', 'menus.id', '=', 'menu_por_rol.menu_id')
            ->where('menu_por_rol.rol_id', $user->rol_id)
            ->where('menu_por_rol.visible', true)
            ->where('menus.activo', true)
            ->select('menus.*')
            ->distinct()
            ->orderBy('menus.orden')
            ->get();
    }

    private function themeForUser($user): array
    {
        $pref = DB::table('preferencias_usuario')
            ->leftJoin('temas', 'preferencias_usuario.tema_id', '=', 'temas.id')
            ->where('preferencias_usuario.usuario_id', $user->id)
            ->select(
                'preferencias_usuario.*',
                'temas.codigo',
                'temas.modo'
            )
            ->first();

        if (!$pref) {
            return [
                'class' => 'theme-youth',
                'fontSize' => 'normal',
                'contrast' => false,
                'auto' => true,
            ];
        }

        $classMap = [
            'NINOS' => 'theme-kids',
            'JOVENES' => 'theme-youth',
            'ADULTOS' => 'theme-adults',
            'DIA' => 'theme-day',
            'NOCHE' => 'theme-night',
            'ALTO_CONTRASTE' => 'theme-contrast',
        ];

        $themeClass = $classMap[$pref->codigo] ?? 'theme-youth';

        if ($pref->contraste === 'Alto') {
            $themeClass = 'theme-contrast';
        }

        return [
            'class' => $themeClass,
            'fontSize' => match ($pref->tamano_fuente) {
                'Grande' => 'grande',
                'Pequeño' => 'pequeno',
                default => 'normal',
            },
            'contrast' => $pref->contraste === 'Alto',
            'auto' => (bool) $pref->modo_automatico,
        ];
    }
}
