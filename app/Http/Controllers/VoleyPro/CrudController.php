<?php

namespace App\Http\Controllers\VoleyPro;

use App\Http\Controllers\Controller;
use App\Support\VoleyProAccess;
use App\Support\VoleyProCrud;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CrudController extends Controller
{
    public function index(Request $request, string $resource)
    {
        $config = VoleyProCrud::get($resource);
        $this->authorizeResource($request, $config, 'puede_ver');

        $query = DB::table($config['table']);

        VoleyProAccess::scopeResource($query, $resource, $config['table'], $request->user());

        if ($request->filled('q') && !empty($config['search'])) {
            $term = '%' . $request->q . '%';

            $query->where(function ($q) use ($config, $term) {
                foreach ($config['search'] as $column) {
                    $q->orWhere($column, 'like', $term);
                }
            });
        }

        return Inertia::render('Crud/Index', [
            'resource' => $resource,
            'config' => $this->frontendConfig($config),
            'items' => $query->orderByDesc('id')->paginate(10)->withQueryString(),
            'filters' => ['q' => $request->q],
            'can' => $this->permissions($request, $config),
        ]);
    }

    public function create(Request $request, string $resource)
    {
        $config = VoleyProCrud::get($resource);
        $this->authorizeResource($request, $config, 'puede_crear');

        return Inertia::render('Crud/Form', [
            'mode' => 'create',
            'resource' => $resource,
            'config' => $this->frontendConfig($config),
            'item' => null,
            'options' => $this->options($request, $config),
        ]);
    }

    public function store(Request $request, string $resource)
    {
        $config = VoleyProCrud::get($resource);
        $this->authorizeResource($request, $config, 'puede_crear');

        $data = $request->validate($this->rules($config, null, true), $this->messages());
        $data = $this->normalizeData($data, $config, true);

        $this->validateBusinessRules($request, $resource, $data, null);

        try {
            DB::transaction(function () use ($resource, $config, $data, &$id) {
                $id = DB::table($config['table'])->insertGetId($data);

                if ($resource === 'inscripciones') {
                    $this->crearPagoInicialParaInscripcion($id);
                }
            });
        } catch (QueryException $e) {
            throw ValidationException::withMessages([
                'general' => 'No se pudo guardar. Verifique duplicados o datos relacionados.',
            ]);
        }

        return redirect()
            ->route('crud.index', $resource)
            ->with('success', 'Registro creado correctamente.');
    }

    public function edit(Request $request, string $resource, int $id)
    {
        $config = VoleyProCrud::get($resource);
        $this->authorizeResource($request, $config, 'puede_editar');

        $query = DB::table($config['table'])->where($config['table'] . '.id', $id);
        VoleyProAccess::scopeResource($query, $resource, $config['table'], $request->user());

        $item = $query->first();

        abort_if(!$item, 404, 'Registro no encontrado o no autorizado.');

        return Inertia::render('Crud/Form', [
            'mode' => 'edit',
            'resource' => $resource,
            'config' => $this->frontendConfig($config),
            'item' => $item,
            'options' => $this->options($request, $config),
        ]);
    }

    public function update(Request $request, string $resource, int $id)
    {
        $config = VoleyProCrud::get($resource);
        $this->authorizeResource($request, $config, 'puede_editar');

        $query = DB::table($config['table'])->where($config['table'] . '.id', $id);
        VoleyProAccess::scopeResource($query, $resource, $config['table'], $request->user());

        abort_if(!$query->first(), 404, 'Registro no encontrado o no autorizado.');

        $data = $request->validate($this->rules($config, $id, false), $this->messages());
        $data = $this->normalizeData($data, $config, false);

        $this->validateBusinessRules($request, $resource, $data, $id);

        try {
            DB::table($config['table'])->where('id', $id)->update($data);
        } catch (QueryException $e) {
            throw ValidationException::withMessages([
                'general' => 'No se pudo actualizar. Verifique duplicados o datos relacionados.',
            ]);
        }

        return redirect()
            ->route('crud.index', $resource)
            ->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy(Request $request, string $resource, int $id)
    {
        $config = VoleyProCrud::get($resource);
        $this->authorizeResource($request, $config, 'puede_eliminar');

        $query = DB::table($config['table'])->where($config['table'] . '.id', $id);
        VoleyProAccess::scopeResource($query, $resource, $config['table'], $request->user());

        abort_if(!$query->first(), 404, 'Registro no encontrado o no autorizado.');

        if ($config['table'] === 'usuarios' && $request->user()->id === $id) {
            return back()->with('error', 'No puede desactivar su propio usuario desde esta pantalla.');
        }

        $this->logicalDelete($resource, $config, $id);

        return redirect()
            ->route('crud.index', $resource)
            ->with('success', 'Registro desactivado correctamente. No se realizó eliminación física.');
    }

    private function normalizeData(array $data, array $config, bool $creating): array
    {
        foreach ($config['fields'] as $field => $meta) {
            if (array_key_exists($field, $data) && !empty($meta['nullable']) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        if (($config['table'] ?? '') === 'usuarios') {
            if (empty($data['password'])) {
                unset($data['password']);
            } else {
                $data['password'] = Hash::make($data['password']);
            }
        }

        if (($config['table'] ?? '') === 'pagos' && empty($data['payment_number'])) {
            $data['payment_number'] = 'VP-' . now()->format('YmdHis') . '-' . random_int(1000, 9999);
        }

        $columns = Schema::getColumnListing($config['table']);

        if ($creating && in_array('creado_en', $columns, true)) {
            $data['creado_en'] = now();
        }

        if ($creating && in_array('fecha', $columns, true) && !array_key_exists('fecha', $data)) {
            $data['fecha'] = now();
        }

        if (in_array('actualizado_en', $columns, true)) {
            $data['actualizado_en'] = now();
        }

        return $data;
    }

    private function rules(array $config, ?int $id, bool $creating): array
    {
        $rules = [];

        foreach ($config['fields'] as $field => $meta) {
            $rules[$field] = $this->toRuleArray($meta['rules'] ?? (!empty($meta['nullable']) ? 'nullable' : 'required'));
        }

        if (($config['table'] ?? '') === 'usuarios') {
            $rules['email'] = [
                'required',
                'email',
                'max:150',
                Rule::unique('usuarios', 'email')->ignore($id),
            ];

            $rules['password'] = $creating
                ? ['required', 'string', 'min:6']
                : ['nullable', 'string', 'min:6'];
        }

        if (($config['table'] ?? '') === 'pagos') {
            $rules['payment_number'] = [
                'nullable',
                'max:100',
                Rule::unique('pagos', 'payment_number')->ignore($id),
            ];
        }

        return $rules;
    }

    private function toRuleArray(array|string $rule): array
    {
        if (is_array($rule)) {
            return $rule;
        }

        return explode('|', $rule);
    }

    private function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'email' => 'El campo :attribute debe tener un correo válido.',
            'unique' => 'El valor del campo :attribute ya está registrado.',
            'min' => 'El campo :attribute debe ser mayor o igual a :min.',
            'max' => 'El campo :attribute no debe superar :max caracteres.',
            'integer' => 'El campo :attribute debe ser un número entero.',
            'numeric' => 'El campo :attribute debe ser numérico.',
            'in' => 'El valor seleccionado en :attribute no es válido.',
            'exists' => 'El valor seleccionado en :attribute no existe.',
        ];
    }

    private function frontendConfig(array $config): array
    {
        return [
            'title' => $config['title'],
            'columns' => $config['columns'],
            'fields' => $config['fields'],
        ];
    }

    private function options(Request $request, array $config): array
    {
        $options = [];

        foreach ($config['fields'] as $field => $meta) {
            if (($meta['type'] ?? null) !== 'select') {
                continue;
            }

            $table = $meta['options']['table'];
            $label = $meta['options']['label'];

            $rows = DB::table($table);

            $this->applyOptionFilters($rows, $meta['options'], $request->user());

            $rows = $rows->orderBy($table . '.id')->limit(300)->get();

            $options[$field] = $rows->map(function ($row) use ($label) {
                if (is_array($label)) {
                    $text = collect($label)
                        ->map(fn ($column) => $row->{$column} ?? '')
                        ->filter()
                        ->join(' ');
                } else {
                    $text = $row->{$label} ?? ('Registro #' . $row->id);
                }

                return [
                    'value' => $row->id,
                    'label' => $text ?: ('Registro #' . $row->id),
                ];
            })->values();
        }

        return $options;
    }

    private function applyOptionFilters($query, array $options, $user): void
    {
        $table = $options['table'];

        if ($table === 'usuarios' && !empty($options['role'])) {
            $query->join('roles', 'usuarios.rol_id', '=', 'roles.id')
                ->where('roles.nombre', $options['role'])
                ->select('usuarios.*');
        }

        if ($table === 'usuarios' && !empty($options['estado'])) {
            $query->where('usuarios.estado', $options['estado']);
        }

        if ($table === 'aulas_canchas') {
            $query->where('estado', 'Disponible');
        }

        if ($table === 'horarios') {
            $query->where('activo', true);
        }

        if ($table === 'metodos_pago') {
            $query->where('activo', true);
        }

        if (!empty($options['estado']) && $table !== 'usuarios') {
            $query->where('estado', $options['estado']);
        }

        $resource = VoleyProAccess::resourceFromTable($table);

        if ($resource) {
            VoleyProAccess::scopeResource($query, $resource, $table, $user);
        }
    }

    private function authorizeResource(Request $request, array $config, string $action): void
    {
        VoleyProAccess::authorize($request, $config['permission'], $action);
    }

    private function permissions(Request $request, array $config): array
    {
        $user = $request->user();

        return [
            'ver' => VoleyProAccess::can($user, $config['permission'], 'puede_ver'),
            'crear' => VoleyProAccess::can($user, $config['permission'], 'puede_crear'),
            'editar' => VoleyProAccess::can($user, $config['permission'], 'puede_editar'),
            'eliminar' => VoleyProAccess::can($user, $config['permission'], 'puede_eliminar'),
        ];
    }

    private function validateBusinessRules(Request $request, string $resource, array $data, ?int $id): void
    {
        match ($resource) {
            'horarios' => $this->validateHorario($data, $id),
            'ofertas-academicas' => $this->validateOferta($data, $id),
            'inscripciones' => $this->validateInscripcion($request, $data, $id),
            'seguimientos' => $this->validateSeguimiento($request, $data),
            'planes-pago' => $this->validatePlanPago($data),
            'cuotas-pago' => $this->validateCuotaPago($data),
            'pagos' => $this->validatePago($request, $data, $id),
            default => null,
        };
    }

    private function validateHorario(array $data, ?int $id): void
    {
        if (strtotime($data['hora_inicio']) >= strtotime($data['hora_fin'])) {
            $this->fail('hora_fin', 'La hora fin debe ser mayor que la hora inicio.');
        }

        if ((bool) ($data['activo'] ?? true)) {
            $exists = DB::table('horarios')
                ->where('dia', $data['dia'])
                ->where('turno', $data['turno'])
                ->where('activo', true)
                ->when($id, fn ($q) => $q->where('id', '<>', $id))
                ->where('hora_inicio', '<', $data['hora_fin'])
                ->where('hora_fin', '>', $data['hora_inicio'])
                ->exists();

            if ($exists) {
                $this->fail('hora_inicio', 'Ya existe un horario activo que se cruza en el mismo día y turno.');
            }
        }
    }

    private function validateOferta(array $data, ?int $id): void
    {
        $docenteValido = DB::table('usuarios')
            ->join('roles', 'usuarios.rol_id', '=', 'roles.id')
            ->where('usuarios.id', $data['docente_id'])
            ->where('usuarios.estado', 'Activo')
            ->where('roles.nombre', 'Docente')
            ->exists();

        if (!$docenteValido) {
            $this->fail('docente_id', 'Debe seleccionar un usuario con rol Docente y estado Activo.');
        }

        $aula = DB::table('aulas_canchas')->where('id', $data['aula_cancha_id'])->first();

        if (!$aula || $aula->estado !== 'Disponible') {
            $this->fail('aula_cancha_id', 'El aula o cancha debe estar Disponible.');
        }

        if ($aula && (int) $data['cupo'] > (int) $aula->capacidad) {
            $this->fail('cupo', 'El cupo no puede superar la capacidad del aula o cancha.');
        }

        $horario = DB::table('horarios')->where('id', $data['horario_id'])->first();

        if (!$horario || !$horario->activo) {
            $this->fail('horario_id', 'Debe seleccionar un horario activo.');
        }

        if (($data['estado'] ?? 'Activa') === 'Activa') {
            $docenteCruce = DB::table('ofertas_academicas')
                ->where('estado', 'Activa')
                ->where('horario_id', $data['horario_id'])
                ->where('docente_id', $data['docente_id'])
                ->when($id, fn ($q) => $q->where('id', '<>', $id))
                ->exists();

            if ($docenteCruce) {
                $this->fail('docente_id', 'El docente ya tiene una oferta activa en ese horario.');
            }

            $aulaCruce = DB::table('ofertas_academicas')
                ->where('estado', 'Activa')
                ->where('horario_id', $data['horario_id'])
                ->where('aula_cancha_id', $data['aula_cancha_id'])
                ->when($id, fn ($q) => $q->where('id', '<>', $id))
                ->exists();

            if ($aulaCruce) {
                $this->fail('aula_cancha_id', 'El aula o cancha ya está ocupada en ese horario.');
            }
        }
    }

    private function validateInscripcion(Request $request, array $data, ?int $id): void
    {
        $alumnoValido = DB::table('usuarios')
            ->join('roles', 'usuarios.rol_id', '=', 'roles.id')
            ->where('usuarios.id', $data['alumno_id'])
            ->where('usuarios.estado', 'Activo')
            ->where('roles.nombre', 'Alumno')
            ->exists();

        if (!$alumnoValido) {
            $this->fail('alumno_id', 'Debe seleccionar un usuario con rol Alumno y estado Activo.');
        }

        if (VoleyProAccess::isAlumno($request->user()) && (int) $data['alumno_id'] !== (int) $request->user()->id) {
            $this->fail('alumno_id', 'El alumno solo puede registrar o ver su propia inscripción.');
        }

        $oferta = DB::table('ofertas_academicas')->where('id', $data['oferta_id'])->first();

        if (!$oferta || $oferta->estado !== 'Activa') {
            $this->fail('oferta_id', 'La oferta académica debe estar Activa.');
        }

        $duplicada = DB::table('inscripciones')
            ->where('alumno_id', $data['alumno_id'])
            ->where('oferta_id', $data['oferta_id'])
            ->when($id, fn ($q) => $q->where('id', '<>', $id))
            ->exists();

        if ($duplicada) {
            $this->fail('oferta_id', 'El alumno ya se encuentra inscrito en esta oferta.');
        }

        if (($data['estado'] ?? 'Activa') === 'Activa' && $oferta) {
            $ocupados = DB::table('inscripciones')
                ->where('oferta_id', $data['oferta_id'])
                ->where('estado', 'Activa')
                ->when($id, fn ($q) => $q->where('id', '<>', $id))
                ->count();

            if ($ocupados >= (int) $oferta->cupo) {
                $this->fail('oferta_id', 'No existen cupos disponibles para esta oferta.');
            }
        }
    }

    private function validateSeguimiento(Request $request, array $data): void
    {
        $inscripcion = DB::table('inscripciones')->where('id', $data['inscripcion_id'])->first();

        if (!$inscripcion || $inscripcion->estado !== 'Activa') {
            $this->fail('inscripcion_id', 'La inscripción debe estar Activa para registrar seguimiento.');
        }

        if (VoleyProAccess::isDocente($request->user())) {
            $own = DB::table('inscripciones')
                ->join('ofertas_academicas', 'inscripciones.oferta_id', '=', 'ofertas_academicas.id')
                ->where('inscripciones.id', $data['inscripcion_id'])
                ->where('ofertas_academicas.docente_id', $request->user()->id)
                ->exists();

            if (!$own) {
                $this->fail('inscripcion_id', 'El docente solo puede registrar seguimiento de sus propias ofertas.');
            }
        }
    }

    private function validatePlanPago(array $data): void
    {
        $inscripcion = DB::table('inscripciones')->where('id', $data['inscripcion_id'])->first();

        if (!$inscripcion || $inscripcion->estado !== 'Activa') {
            $this->fail('inscripcion_id', 'La inscripción debe estar Activa para crear un plan de pago.');
        }
    }

    private function validateCuotaPago(array $data): void
    {
        $plan = DB::table('planes_pago')->where('id', $data['plan_pago_id'])->first();

        if (!$plan || in_array($plan->estado, ['Cancelado', 'Vencido'], true)) {
            $this->fail('plan_pago_id', 'El plan de pago no está disponible para registrar cuotas.');
        }
    }

    private function validatePago(Request $request, array $data, ?int $id): void
    {
        $metodo = DB::table('metodos_pago')->where('id', $data['metodo_pago_id'])->first();

        if (!$metodo || !$metodo->activo) {
            $this->fail('metodo_pago_id', 'El método de pago debe estar activo.');
        }

        $cuota = DB::table('cuotas_pago')
            ->join('planes_pago', 'cuotas_pago.plan_pago_id', '=', 'planes_pago.id')
            ->join('inscripciones', 'planes_pago.inscripcion_id', '=', 'inscripciones.id')
            ->where('cuotas_pago.id', $data['cuota_pago_id'])
            ->select('cuotas_pago.*', 'inscripciones.alumno_id')
            ->first();

        if (!$cuota || in_array($cuota->estado, ['Pagada', 'Cancelada'], true)) {
            $this->fail('cuota_pago_id', 'La cuota no está disponible para pago.');
        }

        if ((int) $data['usuario_id'] !== (int) $cuota->alumno_id) {
            $this->fail('usuario_id', 'El usuario del pago debe ser el alumno dueño de la inscripción.');
        }

        if (VoleyProAccess::isAlumno($request->user()) && (int) $data['usuario_id'] !== (int) $request->user()->id) {
            $this->fail('usuario_id', 'El alumno solo puede registrar pagos propios.');
        }
    }

    private function crearPagoInicialParaInscripcion(int $inscripcionId): void
    {
        if (DB::table('planes_pago')->where('inscripcion_id', $inscripcionId)->exists()) {
            return;
        }

        $inscripcion = DB::table('inscripciones')->where('id', $inscripcionId)->first();

        if (!$inscripcion) {
            return;
        }

        $oferta = DB::table('ofertas_academicas')->where('id', $inscripcion->oferta_id)->first();

        if (!$oferta) {
            return;
        }

        $total = (float) $oferta->precio;

        $planId = DB::table('planes_pago')->insertGetId([
            'inscripcion_id' => $inscripcionId,
            'tipo' => 'Pago único',
            'total' => $total,
            'numero_cuotas' => 1,
            'estado' => $total > 0 ? 'Activo' : 'Pagado',
            'fecha_creacion' => now(),
        ]);

        $cuotaId = DB::table('cuotas_pago')->insertGetId([
            'plan_pago_id' => $planId,
            'numero_cuota' => 1,
            'monto' => $total,
            'fecha_vencimiento' => now()->addDays(10)->toDateString(),
            'estado' => $total > 0 ? 'Pendiente' : 'Pagada',
        ]);

        $metodoId = DB::table('metodos_pago')
            ->where('codigo', 'QR')
            ->where('activo', true)
            ->value('id');

        if (!$metodoId) {
            $metodoId = DB::table('metodos_pago')->where('activo', true)->value('id');
        }

        if ($total > 0 && $metodoId) {
            DB::table('pagos')->insert([
                'cuota_pago_id' => $cuotaId,
                'metodo_pago_id' => $metodoId,
                'usuario_id' => $inscripcion->alumno_id,
                'monto' => $total,
                'fecha' => now(),
                'estado' => 'Pendiente',
                'payment_number' => 'VP-' . $inscripcionId . '-' . now()->format('YmdHis'),
            ]);
        }
    }

    private function logicalDelete(string $resource, array $config, int $id): void
    {
        $table = $config['table'];

        $data = match ($resource) {
            'usuarios' => ['estado' => 'Inactivo'],
            'roles' => ['activo' => false],
            'aulas-canchas' => ['estado' => 'Inactivo'],
            'horarios' => ['activo' => false],
            'ofertas-academicas' => ['estado' => 'Cancelada'],
            'inscripciones' => ['estado' => 'Cancelada'],
            'metodos-pago' => ['activo' => false],
            'planes-pago' => ['estado' => 'Cancelado'],
            'cuotas-pago' => ['estado' => 'Cancelada'],
            'pagos' => ['estado' => 'Cancelado'],
            default => [],
        };

        if (empty($data)) {
            throw ValidationException::withMessages([
                'general' => 'Este módulo conserva historial y no permite eliminación física.',
            ]);
        }

        if (Schema::hasColumn($table, 'actualizado_en')) {
            $data['actualizado_en'] = now();
        }

        DB::table($table)->where('id', $id)->update($data);
    }

    private function fail(string $field, string $message): void
    {
        throw ValidationException::withMessages([
            $field => $message,
        ]);
    }
}
