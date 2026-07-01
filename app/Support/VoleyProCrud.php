<?php

namespace App\Support;

class VoleyProCrud
{
    public static function resources(): array
    {
        return [
            'usuarios' => [
                'title' => 'Usuarios',
                'table' => 'usuarios',
                'permission' => 'usuarios',
                'search' => ['nombre', 'apellido', 'ci', 'telefono', 'email', 'estado'],
                'columns' => [
                    'id' => 'ID',
                    'nombre' => 'Nombre',
                    'apellido' => 'Apellido',
                    'email' => 'Email',
                    'telefono' => 'Teléfono',
                    'estado' => 'Estado',
                    'rol_id' => 'Rol',
                ],
                'fields' => [
                    'nombre' => ['label' => 'Nombre', 'type' => 'text', 'rules' => 'required|max:150'],
                    'apellido' => ['label' => 'Apellido', 'type' => 'text', 'nullable' => true],
                    'ci' => ['label' => 'CI', 'type' => 'text', 'nullable' => true],
                    'telefono' => ['label' => 'Teléfono', 'type' => 'text', 'nullable' => true],
                    'email' => ['label' => 'Email', 'type' => 'email', 'rules' => 'required|email|max:150'],
                    'password' => ['label' => 'Contraseña', 'type' => 'password', 'rules' => 'nullable|min:6', 'nullable' => true],
                    'estado' => ['label' => 'Estado', 'type' => 'select_static', 'options' => ['Activo', 'Inactivo', 'Bloqueado'], 'rules' => 'required'],
                    'rol_id' => ['label' => 'Rol', 'type' => 'select', 'rules' => 'required', 'options' => ['table' => 'roles', 'label' => 'nombre']],
                ],
            ],

            'roles' => [
                'title' => 'Roles',
                'table' => 'roles',
                'permission' => 'seguridad',
                'search' => ['nombre', 'descripcion'],
                'columns' => [
                    'id' => 'ID',
                    'nombre' => 'Nombre',
                    'descripcion' => 'Descripción',
                    'activo' => 'Activo',
                ],
                'fields' => [
                    'nombre' => ['label' => 'Nombre', 'type' => 'select_static', 'options' => ['Propietario', 'Secretaria', 'Docente', 'Alumno'], 'rules' => 'required'],
                    'descripcion' => ['label' => 'Descripción', 'type' => 'text', 'nullable' => true],
                    'activo' => ['label' => 'Activo', 'type' => 'select_static', 'options' => [1, 0], 'rules' => 'required'],
                ],
            ],

            'aulas-canchas' => [
                'title' => 'Aulas y Canchas',
                'table' => 'aulas_canchas',
                'permission' => 'aulas_canchas',
                'search' => ['nombre', 'tipo', 'ubicacion', 'estado'],
                'columns' => [
                    'id' => 'ID',
                    'nombre' => 'Nombre',
                    'tipo' => 'Tipo',
                    'ubicacion' => 'Ubicación',
                    'capacidad' => 'Capacidad',
                    'estado' => 'Estado',
                ],
                'fields' => [
                    'nombre' => ['label' => 'Nombre', 'type' => 'text', 'rules' => 'required|max:150'],
                    'tipo' => ['label' => 'Tipo', 'type' => 'select_static', 'options' => ['Aula', 'Cancha'], 'rules' => 'required'],
                    'ubicacion' => ['label' => 'Ubicación', 'type' => 'text', 'rules' => 'required|max:150'],
                    'tamano' => ['label' => 'Tamaño', 'type' => 'text', 'nullable' => true],
                    'capacidad' => ['label' => 'Capacidad', 'type' => 'number', 'rules' => 'required|integer|min:1'],
                    'estado' => ['label' => 'Estado', 'type' => 'select_static', 'options' => ['Disponible', 'Mantenimiento', 'Inactivo'], 'rules' => 'required'],
                ],
            ],

            'horarios' => [
                'title' => 'Horarios',
                'table' => 'horarios',
                'permission' => 'horarios',
                'search' => ['turno', 'dia'],
                'columns' => [
                    'id' => 'ID',
                    'turno' => 'Turno',
                    'dia' => 'Día',
                    'hora_inicio' => 'Inicio',
                    'hora_fin' => 'Fin',
                    'activo' => 'Activo',
                ],
                'fields' => [
                    'turno' => ['label' => 'Turno', 'type' => 'select_static', 'options' => ['Mañana', 'Tarde', 'Noche'], 'rules' => 'required'],
                    'dia' => ['label' => 'Día', 'type' => 'select_static', 'options' => ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'], 'rules' => 'required'],
                    'hora_inicio' => ['label' => 'Hora inicio', 'type' => 'time', 'rules' => 'required'],
                    'hora_fin' => ['label' => 'Hora fin', 'type' => 'time', 'rules' => 'required'],
                    'activo' => ['label' => 'Activo', 'type' => 'select_static', 'options' => [1, 0], 'rules' => 'required'],
                ],
            ],

            'ofertas-academicas' => [
                'title' => 'Ofertas Académicas',
                'table' => 'ofertas_academicas',
                'permission' => 'ofertas_academicas',
                'search' => ['nombre', 'gestion', 'estado'],
                'columns' => [
                    'id' => 'ID',
                    'nombre' => 'Nombre',
                    'gestion' => 'Gestión',
                    'docente_id' => 'Docente',
                    'aula_cancha_id' => 'Aula/Cancha',
                    'horario_id' => 'Horario',
                    'cupo' => 'Cupo',
                    'precio' => 'Precio',
                    'estado' => 'Estado',
                ],
                'fields' => [
                    'nombre' => ['label' => 'Nombre', 'type' => 'text', 'rules' => 'required|max:150'],
                    'gestion' => ['label' => 'Gestión', 'type' => 'text', 'rules' => 'required|max:30'],
                    'docente_id' => ['label' => 'Docente', 'type' => 'select', 'rules' => 'required', 'options' => ['table' => 'usuarios', 'label' => ['nombre', 'apellido'], 'role' => 'Docente', 'estado' => 'Activo']],
                    'aula_cancha_id' => ['label' => 'Aula/Cancha disponible', 'type' => 'select', 'rules' => 'required', 'options' => ['table' => 'aulas_canchas', 'label' => 'nombre']],
                    'horario_id' => ['label' => 'Horario activo', 'type' => 'select', 'rules' => 'required', 'options' => ['table' => 'horarios', 'label' => ['dia', 'turno']]],
                    'cupo' => ['label' => 'Cupo', 'type' => 'number', 'rules' => 'required|integer|min:1'],
                    'precio' => ['label' => 'Precio', 'type' => 'decimal', 'rules' => 'required|numeric|min:0'],
                    'estado' => ['label' => 'Estado', 'type' => 'select_static', 'options' => ['Activa', 'Inactiva', 'Finalizada', 'Cancelada'], 'rules' => 'required'],
                ],
            ],

            'inscripciones' => [
                'title' => 'Inscripciones',
                'table' => 'inscripciones',
                'permission' => 'inscripciones',
                'search' => ['estado'],
                'columns' => [
                    'id' => 'ID',
                    'alumno_id' => 'Alumno',
                    'oferta_id' => 'Oferta',
                    'fecha' => 'Fecha',
                    'estado' => 'Estado',
                ],
                'fields' => [
                    'alumno_id' => ['label' => 'Alumno', 'type' => 'select', 'rules' => 'required', 'options' => ['table' => 'usuarios', 'label' => ['nombre', 'apellido'], 'role' => 'Alumno', 'estado' => 'Activo']],
                    'oferta_id' => ['label' => 'Oferta activa', 'type' => 'select', 'rules' => 'required', 'options' => ['table' => 'ofertas_academicas', 'label' => 'nombre', 'estado' => 'Activa']],
                    'estado' => ['label' => 'Estado', 'type' => 'select_static', 'options' => ['Activa', 'Retirada', 'Finalizada', 'Cancelada'], 'rules' => 'required'],
                ],
            ],

            'seguimientos' => [
                'title' => 'Seguimientos',
                'table' => 'seguimientos',
                'permission' => 'seguimientos',
                'search' => ['descripcion', 'observacion'],
                'columns' => [
                    'id' => 'ID',
                    'inscripcion_id' => 'Inscripción',
                    'fecha' => 'Fecha',
                    'descripcion' => 'Descripción',
                    'asistencia' => 'Asistencia',
                    'observacion' => 'Observación',
                ],
                'fields' => [
                    'inscripcion_id' => ['label' => 'Inscripción activa', 'type' => 'select', 'rules' => 'required', 'options' => ['table' => 'inscripciones', 'label' => 'id', 'estado' => 'Activa']],
                    'descripcion' => ['label' => 'Descripción', 'type' => 'textarea', 'rules' => 'required'],
                    'asistencia' => ['label' => 'Asistencia', 'type' => 'select_static', 'options' => [1, 0], 'rules' => 'required'],
                    'observacion' => ['label' => 'Observación', 'type' => 'textarea', 'nullable' => true],
                ],
            ],

            'metodos-pago' => [
                'title' => 'Métodos de Pago',
                'table' => 'metodos_pago',
                'permission' => 'pagos',
                'search' => ['nombre', 'codigo', 'descripcion'],
                'columns' => [
                    'id' => 'ID',
                    'nombre' => 'Nombre',
                    'codigo' => 'Código',
                    'descripcion' => 'Descripción',
                    'activo' => 'Activo',
                ],
                'fields' => [
                    'nombre' => ['label' => 'Nombre', 'type' => 'text', 'rules' => 'required|max:80'],
                    'codigo' => ['label' => 'Código', 'type' => 'text', 'rules' => 'required|max:50'],
                    'descripcion' => ['label' => 'Descripción', 'type' => 'text', 'nullable' => true],
                    'activo' => ['label' => 'Activo', 'type' => 'select_static', 'options' => [1, 0], 'rules' => 'required'],
                ],
            ],

            'planes-pago' => [
                'title' => 'Planes de Pago',
                'table' => 'planes_pago',
                'permission' => 'planes_pago',
                'search' => ['tipo', 'estado'],
                'columns' => [
                    'id' => 'ID',
                    'inscripcion_id' => 'Inscripción',
                    'tipo' => 'Tipo',
                    'total' => 'Total',
                    'numero_cuotas' => 'Cuotas',
                    'estado' => 'Estado',
                ],
                'fields' => [
                    'inscripcion_id' => ['label' => 'Inscripción activa', 'type' => 'select', 'rules' => 'required', 'options' => ['table' => 'inscripciones', 'label' => 'id', 'estado' => 'Activa']],
                    'tipo' => ['label' => 'Tipo', 'type' => 'select_static', 'options' => ['Pago único', 'Plan de pagos'], 'rules' => 'required'],
                    'total' => ['label' => 'Total', 'type' => 'decimal', 'rules' => 'required|numeric|min:0'],
                    'numero_cuotas' => ['label' => 'Número de cuotas', 'type' => 'number', 'rules' => 'required|integer|min:1'],
                    'estado' => ['label' => 'Estado', 'type' => 'select_static', 'options' => ['Pendiente', 'Activo', 'Pagado', 'Cancelado', 'Vencido'], 'rules' => 'required'],
                ],
            ],

            'cuotas-pago' => [
                'title' => 'Cuotas de Pago',
                'table' => 'cuotas_pago',
                'permission' => 'cuotas_pago',
                'search' => ['estado'],
                'columns' => [
                    'id' => 'ID',
                    'plan_pago_id' => 'Plan',
                    'numero_cuota' => 'Nro.',
                    'monto' => 'Monto',
                    'fecha_vencimiento' => 'Vencimiento',
                    'estado' => 'Estado',
                ],
                'fields' => [
                    'plan_pago_id' => ['label' => 'Plan de pago', 'type' => 'select', 'rules' => 'required', 'options' => ['table' => 'planes_pago', 'label' => 'id']],
                    'numero_cuota' => ['label' => 'Número de cuota', 'type' => 'number', 'rules' => 'required|integer|min:1'],
                    'monto' => ['label' => 'Monto', 'type' => 'decimal', 'rules' => 'required|numeric|min:0'],
                    'fecha_vencimiento' => ['label' => 'Fecha vencimiento', 'type' => 'date', 'nullable' => true],
                    'estado' => ['label' => 'Estado', 'type' => 'select_static', 'options' => ['Pendiente', 'Pagada', 'Vencida', 'Cancelada'], 'rules' => 'required'],
                ],
            ],

            'pagos' => [
                'title' => 'Pagos',
                'table' => 'pagos',
                'permission' => 'pagos',
                'search' => ['estado', 'payment_number', 'pagofacil_status'],
                'columns' => [
                    'id' => 'ID',
                    'cuota_pago_id' => 'Cuota',
                    'metodo_pago_id' => 'Método',
                    'usuario_id' => 'Usuario',
                    'monto' => 'Monto',
                    'estado' => 'Estado',
                    'payment_number' => 'Payment Number',
                    'pagofacil_transaction_id' => 'PagoFácil ID',
                ],
                'fields' => [
                    'cuota_pago_id' => ['label' => 'Cuota pendiente', 'type' => 'select', 'rules' => 'required', 'options' => ['table' => 'cuotas_pago', 'label' => 'id', 'estado' => 'Pendiente']],
                    'metodo_pago_id' => ['label' => 'Método de pago', 'type' => 'select', 'rules' => 'required', 'options' => ['table' => 'metodos_pago', 'label' => 'nombre']],
                    'usuario_id' => ['label' => 'Usuario alumno', 'type' => 'select', 'rules' => 'required', 'options' => ['table' => 'usuarios', 'label' => ['nombre', 'apellido'], 'role' => 'Alumno', 'estado' => 'Activo']],
                    'monto' => ['label' => 'Monto', 'type' => 'decimal', 'rules' => 'required|numeric|min:0'],
                    'estado' => ['label' => 'Estado', 'type' => 'select_static', 'options' => ['Pendiente', 'Revision', 'Pagado', 'Expirado', 'Cancelado', 'Rechazado'], 'rules' => 'required'],
                    'payment_number' => ['label' => 'Payment Number', 'type' => 'text', 'rules' => 'nullable|max:100', 'nullable' => true],
                ],
            ],
        ];
    }

    public static function get(string $resource): array
    {
        $resources = self::resources();

        abort_unless(isset($resources[$resource]), 404, 'Recurso no encontrado.');

        return $resources[$resource];
    }
}
