<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50)->unique();
            $table->string('descripcion', 150)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
        });

        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('apellido', 150)->nullable();
            $table->string('ci', 30)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('email', 150)->unique();
            $table->string('password', 255);
            $table->string('estado', 30)->default('Activo');
            $table->foreignId('rol_id')->constrained('roles')->restrictOnDelete()->cascadeOnUpdate();
            $table->timestamp('creado_en')->useCurrent();
            $table->timestamp('actualizado_en')->useCurrent();
        });

        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('ruta', 150)->unique();
            $table->string('icono', 80)->nullable();
            $table->integer('orden')->default(0);
            $table->foreignId('menu_padre_id')->nullable()->constrained('menus')->nullOnDelete()->cascadeOnUpdate();
            $table->boolean('activo')->default(true);
        });

        Schema::create('permisos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('codigo', 100)->unique();
            $table->string('modulo', 100);
            $table->string('descripcion', 150)->nullable();
        });

        Schema::create('menu_por_rol', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rol_id')->constrained('roles')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete()->cascadeOnUpdate();
            $table->boolean('visible')->default(true);
            $table->timestamp('creado_en')->useCurrent();

            $table->unique(['rol_id', 'menu_id']);
        });

        Schema::create('matriz_acceso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rol_id')->constrained('roles')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('permiso_id')->constrained('permisos')->cascadeOnDelete()->cascadeOnUpdate();
            $table->boolean('puede_ver')->default(false);
            $table->boolean('puede_crear')->default(false);
            $table->boolean('puede_editar')->default(false);
            $table->boolean('puede_eliminar')->default(false);

            $table->unique(['rol_id', 'permiso_id']);
        });

        Schema::create('bitacora_accesos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete()->cascadeOnUpdate();
            $table->string('email_intento', 150)->nullable();
            $table->string('tipo', 30);
            $table->string('ip', 50)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('descripcion')->nullable();
            $table->timestamp('fecha')->useCurrent();
        });

        Schema::create('aulas_canchas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('tipo', 30);
            $table->string('ubicacion', 150);
            $table->string('tamano', 50)->nullable();
            $table->integer('capacidad');
            $table->string('estado', 30)->default('Disponible');
        });

        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->string('turno', 30);
            $table->string('dia', 30);
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->boolean('activo')->default(true);
        });

        Schema::create('ofertas_academicas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('gestion', 30);
            $table->foreignId('docente_id')->constrained('usuarios')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('aula_cancha_id')->constrained('aulas_canchas')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('horario_id')->constrained('horarios')->restrictOnDelete()->cascadeOnUpdate();
            $table->integer('cupo');
            $table->decimal('precio', 10, 2)->default(0);
            $table->string('estado', 30)->default('Activa');
        });

        Schema::create('inscripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('usuarios')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('oferta_id')->constrained('ofertas_academicas')->restrictOnDelete()->cascadeOnUpdate();
            $table->timestamp('fecha')->useCurrent();
            $table->string('estado', 30)->default('Activa');

            $table->unique(['alumno_id', 'oferta_id']);
        });

        Schema::create('seguimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscripcion_id')->constrained('inscripciones')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamp('fecha')->useCurrent();
            $table->text('descripcion');
            $table->boolean('asistencia')->default(false);
            $table->text('observacion')->nullable();
        });

        Schema::create('metodos_pago', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80);
            $table->string('codigo', 50)->unique();
            $table->string('descripcion', 150)->nullable();
            $table->boolean('activo')->default(true);
        });

        Schema::create('planes_pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscripcion_id')->unique()->constrained('inscripciones')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('tipo', 30);
            $table->decimal('total', 10, 2);
            $table->integer('numero_cuotas')->default(1);
            $table->string('estado', 30)->default('Pendiente');
            $table->timestamp('fecha_creacion')->useCurrent();
        });

        Schema::create('cuotas_pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_pago_id')->constrained('planes_pago')->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('numero_cuota');
            $table->decimal('monto', 10, 2);
            $table->date('fecha_vencimiento')->nullable();
            $table->string('estado', 30)->default('Pendiente');

            $table->unique(['plan_pago_id', 'numero_cuota']);
        });

        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuota_pago_id')->constrained('cuotas_pago')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('metodo_pago_id')->constrained('metodos_pago')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreignId('usuario_id')->constrained('usuarios')->restrictOnDelete()->cascadeOnUpdate();

            $table->decimal('monto', 10, 2);
            $table->timestamp('fecha')->useCurrent();
            $table->string('estado', 30)->default('Pendiente');
            $table->string('payment_number', 100)->unique();

            $table->bigInteger('pagofacil_transaction_id')->nullable();
            $table->string('payment_method_transaction_id', 100)->nullable();
            $table->string('pagofacil_status', 30)->nullable();
            $table->string('pagofacil_payment_date', 50)->nullable();
            $table->string('pagofacil_payment_time', 50)->nullable();
            $table->timestamp('fecha_verificacion')->nullable();

            $table->text('qr_base64')->nullable();
            $table->text('checkout_url')->nullable();
            $table->text('deep_link')->nullable();
            $table->text('qr_content_url')->nullable();
            $table->text('universal_url')->nullable();
            $table->string('expiration_date', 50)->nullable();
        });

        Schema::create('temas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80);
            $table->string('codigo', 50)->unique();
            $table->string('descripcion', 150)->nullable();
            $table->string('modo', 30);
            $table->boolean('activo')->default(true);
        });

        Schema::create('preferencias_usuario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->unique()->constrained('usuarios')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('tema_id')->constrained('temas')->restrictOnDelete()->cascadeOnUpdate();
            $table->string('tamano_fuente', 30)->default('Normal');
            $table->string('contraste', 30)->default('Normal');
            $table->boolean('modo_automatico')->default(true);
        });

        Schema::create('paginas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('ruta', 150)->unique();
            $table->integer('contador_visitas')->default(0);
            $table->boolean('activo')->default(true);
        });

        Schema::create('visitas_pagina', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pagina_id')->constrained('paginas')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete()->cascadeOnUpdate();
            $table->string('ip', 50)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('fecha')->useCurrent();
        });

        Schema::create('busquedas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete()->cascadeOnUpdate();
            $table->string('termino', 150);
            $table->string('modulo', 100)->nullable();
            $table->integer('resultados')->default(0);
            $table->timestamp('fecha')->useCurrent();
        });

        Schema::create('bitacora_recursos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('pagina_id')->nullable()->constrained('paginas')->nullOnDelete()->cascadeOnUpdate();
            $table->string('ruta', 150);
            $table->string('metodo', 20);
            $table->string('accion', 100)->nullable();
            $table->timestamp('fecha')->useCurrent();
        });

        $this->crearIndices();

        if (DB::connection()->getDriverName() === 'pgsql') {
            $this->crearTriggersPostgres();
        }
    }

    private function crearIndices(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->index('rol_id');
            $table->index('email');
        });

        Schema::table('menu_por_rol', function (Blueprint $table) {
            $table->index('rol_id');
            $table->index('menu_id');
        });

        Schema::table('matriz_acceso', function (Blueprint $table) {
            $table->index('rol_id');
            $table->index('permiso_id');
        });

        Schema::table('ofertas_academicas', function (Blueprint $table) {
            $table->index('docente_id');
            $table->index('aula_cancha_id');
            $table->index('horario_id');
        });

        Schema::table('inscripciones', function (Blueprint $table) {
            $table->index('alumno_id');
            $table->index('oferta_id');
        });

        Schema::table('seguimientos', function (Blueprint $table) {
            $table->index('inscripcion_id');
        });

        Schema::table('planes_pago', function (Blueprint $table) {
            $table->index('inscripcion_id');
        });

        Schema::table('cuotas_pago', function (Blueprint $table) {
            $table->index('plan_pago_id');
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->index('cuota_pago_id');
            $table->index('metodo_pago_id');
            $table->index('usuario_id');
        });

        Schema::table('bitacora_accesos', function (Blueprint $table) {
            $table->index('usuario_id');
        });

        Schema::table('bitacora_recursos', function (Blueprint $table) {
            $table->index('usuario_id');
            $table->index('pagina_id');
        });

        Schema::table('visitas_pagina', function (Blueprint $table) {
            $table->index('pagina_id');
            $table->index('usuario_id');
        });

        Schema::table('busquedas', function (Blueprint $table) {
            $table->index('usuario_id');
        });
    }

    private function crearTriggersPostgres(): void
    {
        DB::unprepared("
            CREATE OR REPLACE FUNCTION fn_actualizar_fecha_modificacion()
            RETURNS TRIGGER AS $$
            BEGIN
                NEW.actualizado_en = CURRENT_TIMESTAMP;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER trg_usuarios_actualizado_en
            BEFORE UPDATE ON usuarios
            FOR EACH ROW
            EXECUTE FUNCTION fn_actualizar_fecha_modificacion();

            CREATE OR REPLACE FUNCTION fn_incrementar_contador_visitas()
            RETURNS TRIGGER AS $$
            BEGIN
                UPDATE paginas
                SET contador_visitas = contador_visitas + 1
                WHERE id = NEW.pagina_id;

                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;

            CREATE TRIGGER trg_incrementar_contador_visitas
            AFTER INSERT ON visitas_pagina
            FOR EACH ROW
            EXECUTE FUNCTION fn_incrementar_contador_visitas();
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('bitacora_recursos');
        Schema::dropIfExists('busquedas');
        Schema::dropIfExists('visitas_pagina');
        Schema::dropIfExists('paginas');
        Schema::dropIfExists('preferencias_usuario');
        Schema::dropIfExists('temas');
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('cuotas_pago');
        Schema::dropIfExists('planes_pago');
        Schema::dropIfExists('metodos_pago');
        Schema::dropIfExists('seguimientos');
        Schema::dropIfExists('inscripciones');
        Schema::dropIfExists('ofertas_academicas');
        Schema::dropIfExists('horarios');
        Schema::dropIfExists('aulas_canchas');
        Schema::dropIfExists('bitacora_accesos');
        Schema::dropIfExists('matriz_acceso');
        Schema::dropIfExists('menu_por_rol');
        Schema::dropIfExists('permisos');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('usuarios');
        Schema::dropIfExists('roles');
    }
};
