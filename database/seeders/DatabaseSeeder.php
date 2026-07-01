<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $roles = [
            ['nombre' => 'Propietario', 'descripcion' => 'Responsable principal de la escuela de vóley.'],
            ['nombre' => 'Secretaria', 'descripcion' => 'Encargada de operaciones administrativas.'],
            ['nombre' => 'Docente', 'descripcion' => 'Encargado de clases, horarios y seguimiento deportivo.'],
            ['nombre' => 'Alumno', 'descripcion' => 'Usuario inscrito en las ofertas deportivas.'],
        ];

        foreach ($roles as $rol) {
            DB::table('roles')->updateOrInsert(
                ['nombre' => $rol['nombre']],
                [
                    'descripcion' => $rol['descripcion'],
                    'activo' => true,
                    'creado_en' => $now,
                ]
            );
        }

        $rolIds = DB::table('roles')->pluck('id', 'nombre');

        $usuarios = [
            [
                'nombre' => 'Joel',
                'apellido' => 'Propietario',
                'ci' => '1000001',
                'telefono' => '70000001',
                'email' => 'propietario@voleypro.test',
                'rol_id' => $rolIds['Propietario'],
            ],
            [
                'nombre' => 'Carla',
                'apellido' => 'Secretaria',
                'ci' => '1000002',
                'telefono' => '70000002',
                'email' => 'secretaria@voleypro.test',
                'rol_id' => $rolIds['Secretaria'],
            ],
            [
                'nombre' => 'Carlos',
                'apellido' => 'Roca',
                'ci' => '1000003',
                'telefono' => '70000003',
                'email' => 'docente@voleypro.test',
                'rol_id' => $rolIds['Docente'],
            ],
            [
                'nombre' => 'Luis',
                'apellido' => 'López',
                'ci' => '1000004',
                'telefono' => '70000004',
                'email' => 'alumno@voleypro.test',
                'rol_id' => $rolIds['Alumno'],
            ],
        ];

        foreach ($usuarios as $usuario) {
            DB::table('usuarios')->updateOrInsert(
                ['email' => $usuario['email']],
                array_merge($usuario, [
                    'password' => Hash::make('password'),
                    'estado' => 'Activo',
                    'creado_en' => $now,
                    'actualizado_en' => $now,
                ])
            );
        }

        $usuarioIds = DB::table('usuarios')->pluck('id', 'email');

        $menus = [
            ['nombre' => 'Dashboard', 'ruta' => '/dashboard', 'icono' => 'LayoutDashboard', 'orden' => 1],
            ['nombre' => 'Usuarios', 'ruta' => '/usuarios', 'icono' => 'Users', 'orden' => 2],
            ['nombre' => 'Aulas y Canchas', 'ruta' => '/aulas-canchas', 'icono' => 'MapPin', 'orden' => 3],
            ['nombre' => 'Horarios', 'ruta' => '/horarios', 'icono' => 'CalendarDays', 'orden' => 4],
            ['nombre' => 'Ofertas Académicas', 'ruta' => '/ofertas-academicas', 'icono' => 'Volleyball', 'orden' => 5],
            ['nombre' => 'Inscripciones', 'ruta' => '/inscripciones', 'icono' => 'ClipboardList', 'orden' => 6],
            ['nombre' => 'Seguimientos', 'ruta' => '/seguimientos', 'icono' => 'CheckCircle', 'orden' => 7],
            ['nombre' => 'Planes de Pago', 'ruta' => '/planes-pago', 'icono' => 'WalletCards', 'orden' => 8],
            ['nombre' => 'Cuotas', 'ruta' => '/cuotas-pago', 'icono' => 'Receipt', 'orden' => 9],
            ['nombre' => 'Pagos', 'ruta' => '/pagos', 'icono' => 'CreditCard', 'orden' => 10],
            ['nombre' => 'Reportes', 'ruta' => '/reportes', 'icono' => 'BarChart3', 'orden' => 11],
            ['nombre' => 'Seguridad', 'ruta' => '/seguridad', 'icono' => 'Shield', 'orden' => 12],
            ['nombre' => 'Bitácora', 'ruta' => '/bitacora', 'icono' => 'History', 'orden' => 13],
            ['nombre' => 'Temas', 'ruta' => '/tema', 'icono' => 'Palette', 'orden' => 14],
        ];

        foreach ($menus as $menu) {
            DB::table('menus')->updateOrInsert(
                ['ruta' => $menu['ruta']],
                array_merge($menu, [
                    'menu_padre_id' => null,
                    'activo' => true,
                ])
            );
        }

        $menuIds = DB::table('menus')->pluck('id', 'nombre');

        $permisos = [
            ['codigo' => 'usuarios', 'nombre' => 'Gestión de usuarios', 'modulo' => 'Seguridad'],
            ['codigo' => 'aulas_canchas', 'nombre' => 'Gestión de aulas y canchas', 'modulo' => 'Académico'],
            ['codigo' => 'horarios', 'nombre' => 'Gestión de horarios', 'modulo' => 'Académico'],
            ['codigo' => 'ofertas_academicas', 'nombre' => 'Gestión de ofertas académicas', 'modulo' => 'Académico'],
            ['codigo' => 'inscripciones', 'nombre' => 'Gestión de inscripciones', 'modulo' => 'Académico'],
            ['codigo' => 'seguimientos', 'nombre' => 'Seguimiento deportivo', 'modulo' => 'Académico'],
            ['codigo' => 'planes_pago', 'nombre' => 'Gestión de planes de pago', 'modulo' => 'Pagos'],
            ['codigo' => 'cuotas_pago', 'nombre' => 'Gestión de cuotas', 'modulo' => 'Pagos'],
            ['codigo' => 'pagos', 'nombre' => 'Gestión de pagos electrónicos', 'modulo' => 'Pagos'],
            ['codigo' => 'reportes', 'nombre' => 'Reportes y estadísticas', 'modulo' => 'Reportes'],
            ['codigo' => 'seguridad', 'nombre' => 'Matriz de acceso y menú dinámico', 'modulo' => 'Seguridad'],
            ['codigo' => 'bitacora', 'nombre' => 'Bitácora de accesos y recursos', 'modulo' => 'Seguridad'],
            ['codigo' => 'temas', 'nombre' => 'Temas y accesibilidad', 'modulo' => 'Experiencia Web'],
        ];

        foreach ($permisos as $permiso) {
            DB::table('permisos')->updateOrInsert(
                ['codigo' => $permiso['codigo']],
                array_merge($permiso, [
                    'descripcion' => $permiso['nombre'],
                ])
            );
        }

        $permisoIds = DB::table('permisos')->pluck('id', 'codigo');

        $menusPorRol = [
            'Propietario' => array_keys($menuIds->toArray()),
            'Secretaria' => [
                'Dashboard',
                'Usuarios',
                'Aulas y Canchas',
                'Horarios',
                'Ofertas Académicas',
                'Inscripciones',
                'Planes de Pago',
                'Cuotas',
                'Pagos',
                'Reportes',
                'Temas',
            ],
            'Docente' => [
                'Dashboard',
                'Horarios',
                'Ofertas Académicas',
                'Inscripciones',
                'Seguimientos',
                'Reportes',
                'Temas',
            ],
            'Alumno' => [
                'Dashboard',
                'Horarios',
                'Ofertas Académicas',
                'Inscripciones',
                'Planes de Pago',
                'Cuotas',
                'Pagos',
                'Temas',
            ],
        ];

        foreach ($menusPorRol as $rol => $listaMenus) {
            foreach ($listaMenus as $menuNombre) {
                if (!isset($menuIds[$menuNombre])) {
                    continue;
                }

                DB::table('menu_por_rol')->updateOrInsert(
                    [
                        'rol_id' => $rolIds[$rol],
                        'menu_id' => $menuIds[$menuNombre],
                    ],
                    [
                        'visible' => true,
                        'creado_en' => $now,
                    ]
                );
            }
        }

        $matriz = [
            'Propietario' => [
                'usuarios' => [1, 1, 1, 1],
                'aulas_canchas' => [1, 1, 1, 1],
                'horarios' => [1, 1, 1, 1],
                'ofertas_academicas' => [1, 1, 1, 1],
                'inscripciones' => [1, 1, 1, 1],
                'seguimientos' => [1, 1, 1, 1],
                'planes_pago' => [1, 1, 1, 1],
                'cuotas_pago' => [1, 1, 1, 1],
                'pagos' => [1, 1, 1, 1],
                'reportes' => [1, 0, 0, 0],
                'seguridad' => [1, 1, 1, 1],
                'bitacora' => [1, 0, 0, 0],
                'temas' => [1, 1, 1, 0],
            ],
            'Secretaria' => [
                'usuarios' => [1, 1, 1, 0],
                'aulas_canchas' => [1, 1, 1, 0],
                'horarios' => [1, 1, 1, 0],
                'ofertas_academicas' => [1, 1, 1, 0],
                'inscripciones' => [1, 1, 1, 0],
                'seguimientos' => [1, 0, 0, 0],
                'planes_pago' => [1, 1, 1, 0],
                'cuotas_pago' => [1, 1, 1, 0],
                'pagos' => [1, 1, 1, 0],
                'reportes' => [1, 0, 0, 0],
                'seguridad' => [0, 0, 0, 0],
                'bitacora' => [0, 0, 0, 0],
                'temas' => [1, 0, 1, 0],
            ],
            'Docente' => [
                'usuarios' => [1, 0, 0, 0],
                'aulas_canchas' => [1, 0, 0, 0],
                'horarios' => [1, 0, 0, 0],
                'ofertas_academicas' => [1, 0, 0, 0],
                'inscripciones' => [1, 0, 0, 0],
                'seguimientos' => [1, 1, 1, 0],
                'planes_pago' => [0, 0, 0, 0],
                'cuotas_pago' => [0, 0, 0, 0],
                'pagos' => [0, 0, 0, 0],
                'reportes' => [1, 0, 0, 0],
                'seguridad' => [0, 0, 0, 0],
                'bitacora' => [0, 0, 0, 0],
                'temas' => [1, 0, 1, 0],
            ],
            'Alumno' => [
                'usuarios' => [1, 0, 0, 0],
                'aulas_canchas' => [0, 0, 0, 0],
                'horarios' => [1, 0, 0, 0],
                'ofertas_academicas' => [1, 0, 0, 0],
                'inscripciones' => [1, 0, 0, 0],
                'seguimientos' => [1, 0, 0, 0],
                'planes_pago' => [1, 0, 0, 0],
                'cuotas_pago' => [1, 0, 0, 0],
                'pagos' => [1, 0, 0, 0],
                'reportes' => [0, 0, 0, 0],
                'seguridad' => [0, 0, 0, 0],
                'bitacora' => [0, 0, 0, 0],
                'temas' => [1, 0, 1, 0],
            ],
        ];

        foreach ($matriz as $rol => $permisosRol) {
            foreach ($permisosRol as $codigo => $acciones) {
                DB::table('matriz_acceso')->updateOrInsert(
                    [
                        'rol_id' => $rolIds[$rol],
                        'permiso_id' => $permisoIds[$codigo],
                    ],
                    [
                        'puede_ver' => (bool) $acciones[0],
                        'puede_crear' => (bool) $acciones[1],
                        'puede_editar' => (bool) $acciones[2],
                        'puede_eliminar' => (bool) $acciones[3],
                    ]
                );
            }
        }

        DB::table('aulas_canchas')->insert([
            [
                'nombre' => 'Cancha Principal VoleyPro',
                'tipo' => 'Cancha',
                'ubicacion' => 'Sede central',
                'tamano' => '18x9',
                'capacidad' => 24,
                'estado' => 'Disponible',
            ],
            [
                'nombre' => 'Aula Técnica',
                'tipo' => 'Aula',
                'ubicacion' => 'Bloque académico',
                'tamano' => 'Mediana',
                'capacidad' => 30,
                'estado' => 'Disponible',
            ],
        ]);

        DB::table('horarios')->insert([
            [
                'turno' => 'Tarde',
                'dia' => 'Lunes',
                'hora_inicio' => '16:00:00',
                'hora_fin' => '18:00:00',
                'activo' => true,
            ],
            [
                'turno' => 'Noche',
                'dia' => 'Miércoles',
                'hora_inicio' => '19:00:00',
                'hora_fin' => '21:00:00',
                'activo' => true,
            ],
        ]);

        $canchaId = DB::table('aulas_canchas')->where('tipo', 'Cancha')->value('id');
        $horarioId = DB::table('horarios')->where('dia', 'Lunes')->value('id');

        DB::table('ofertas_academicas')->insert([
            'nombre' => 'Vóley Jóvenes Inicial',
            'gestion' => '2026',
            'docente_id' => $usuarioIds['docente@voleypro.test'],
            'aula_cancha_id' => $canchaId,
            'horario_id' => $horarioId,
            'cupo' => 20,
            'precio' => 220,
            'estado' => 'Activa',
        ]);

        $ofertaId = DB::table('ofertas_academicas')->where('nombre', 'Vóley Jóvenes Inicial')->value('id');

        DB::table('inscripciones')->insert([
            'alumno_id' => $usuarioIds['alumno@voleypro.test'],
            'oferta_id' => $ofertaId,
            'fecha' => $now,
            'estado' => 'Activa',
        ]);

        $inscripcionId = DB::table('inscripciones')->value('id');

        DB::table('seguimientos')->insert([
            'inscripcion_id' => $inscripcionId,
            'fecha' => $now,
            'descripcion' => 'Primera clase de fundamentos de vóley.',
            'asistencia' => true,
            'observacion' => 'Buen rendimiento inicial.',
        ]);

        DB::table('metodos_pago')->updateOrInsert(
            ['codigo' => 'QR'],
            [
                'nombre' => 'Pago QR',
                'descripcion' => 'Pago electrónico mediante código QR.',
                'activo' => true,
            ]
        );

        DB::table('planes_pago')->insert([
            'inscripcion_id' => $inscripcionId,
            'tipo' => 'Plan de pagos',
            'total' => 220,
            'numero_cuotas' => 2,
            'estado' => 'Activo',
            'fecha_creacion' => $now,
        ]);

        $planPagoId = DB::table('planes_pago')->value('id');

        DB::table('cuotas_pago')->insert([
            [
                'plan_pago_id' => $planPagoId,
                'numero_cuota' => 1,
                'monto' => 110,
                'fecha_vencimiento' => now()->addDays(10)->toDateString(),
                'estado' => 'Pendiente',
            ],
            [
                'plan_pago_id' => $planPagoId,
                'numero_cuota' => 2,
                'monto' => 110,
                'fecha_vencimiento' => now()->addDays(40)->toDateString(),
                'estado' => 'Pendiente',
            ],
        ]);

        $cuotaId = DB::table('cuotas_pago')->where('numero_cuota', 1)->value('id');
        $metodoId = DB::table('metodos_pago')->where('codigo', 'QR')->value('id');

        DB::table('pagos')->insert([
            'cuota_pago_id' => $cuotaId,
            'metodo_pago_id' => $metodoId,
            'usuario_id' => $usuarioIds['alumno@voleypro.test'],
            'monto' => 110,
            'fecha' => $now,
            'estado' => 'Pendiente',
            'payment_number' => 'VP-PAY-000001',
            'pagofacil_transaction_id' => null,
            'payment_method_transaction_id' => null,
            'pagofacil_status' => null,
            'pagofacil_payment_date' => null,
            'pagofacil_payment_time' => null,
            'fecha_verificacion' => null,
            'qr_base64' => null,
            'checkout_url' => null,
            'deep_link' => null,
            'qr_content_url' => null,
            'universal_url' => null,
            'expiration_date' => null,
        ]);

        $temas = [
            ['nombre' => 'Tema Niños', 'codigo' => 'NINOS', 'descripcion' => 'Tema visual orientado a niños.', 'modo' => 'Niños'],
            ['nombre' => 'Tema Jóvenes', 'codigo' => 'JOVENES', 'descripcion' => 'Tema visual orientado a jóvenes.', 'modo' => 'Jóvenes'],
            ['nombre' => 'Tema Adultos', 'codigo' => 'ADULTOS', 'descripcion' => 'Tema visual orientado a adultos.', 'modo' => 'Adultos'],
            ['nombre' => 'Modo Día', 'codigo' => 'DIA', 'descripcion' => 'Tema claro según horario del cliente.', 'modo' => 'Día'],
            ['nombre' => 'Modo Noche', 'codigo' => 'NOCHE', 'descripcion' => 'Tema oscuro según horario del cliente.', 'modo' => 'Noche'],
            ['nombre' => 'Alto Contraste', 'codigo' => 'ALTO_CONTRASTE', 'descripcion' => 'Tema de accesibilidad con mayor contraste.', 'modo' => 'Alto contraste'],
        ];

        foreach ($temas as $tema) {
            DB::table('temas')->updateOrInsert(['codigo' => $tema['codigo']], array_merge($tema, ['activo' => true]));
        }

        $temaJovenesId = DB::table('temas')->where('codigo', 'JOVENES')->value('id');

        foreach ($usuarioIds as $usuarioId) {
            DB::table('preferencias_usuario')->updateOrInsert(
                ['usuario_id' => $usuarioId],
                [
                    'tema_id' => $temaJovenesId,
                    'tamano_fuente' => 'Normal',
                    'contraste' => 'Normal',
                    'modo_automatico' => true,
                ]
            );
        }

        $paginas = [
            ['nombre' => 'Dashboard', 'ruta' => '/dashboard'],
            ['nombre' => 'Usuarios', 'ruta' => '/usuarios'],
            ['nombre' => 'Aulas y Canchas', 'ruta' => '/aulas-canchas'],
            ['nombre' => 'Horarios', 'ruta' => '/horarios'],
            ['nombre' => 'Ofertas Académicas', 'ruta' => '/ofertas-academicas'],
            ['nombre' => 'Inscripciones', 'ruta' => '/inscripciones'],
            ['nombre' => 'Seguimientos', 'ruta' => '/seguimientos'],
            ['nombre' => 'Planes de Pago', 'ruta' => '/planes-pago'],
            ['nombre' => 'Cuotas', 'ruta' => '/cuotas-pago'],
            ['nombre' => 'Pagos', 'ruta' => '/pagos'],
            ['nombre' => 'Reportes', 'ruta' => '/reportes'],
            ['nombre' => 'Seguridad', 'ruta' => '/seguridad'],
            ['nombre' => 'Bitácora', 'ruta' => '/bitacora'],
            ['nombre' => 'Tema', 'ruta' => '/tema'],
        ];

        foreach ($paginas as $pagina) {
            DB::table('paginas')->updateOrInsert(
                ['ruta' => $pagina['ruta']],
                [
                    'nombre' => $pagina['nombre'],
                    'contador_visitas' => 0,
                    'activo' => true,
                ]
            );
        }

        DB::table('bitacora_accesos')->insert([
            'usuario_id' => $usuarioIds['propietario@voleypro.test'],
            'email_intento' => 'propietario@voleypro.test',
            'tipo' => 'LOGIN_OK',
            'ip' => '127.0.0.1',
            'user_agent' => 'Seeder Laravel',
            'descripcion' => 'Registro inicial de bitácora.',
            'fecha' => $now,
        ]);
    }
}
