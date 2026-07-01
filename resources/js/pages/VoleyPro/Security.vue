<script setup>
import VoleyProLayout from '@/layouts/VoleyProLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    roles: Array,
    permisos: Array,
    matriz: Array,
});
</script>

<template>
    <Head title="Seguridad" />

    <VoleyProLayout>
        <div class="vp-page-head">
            <div>
                <h1>Matriz de acceso</h1>
                <p>
                    Administrador no es un rol. Los roles del negocio controlan
                    permisos y menú.
                </p>
            </div>
        </div>

        <div class="vp-card">
            <table class="vp-table">
                <thead>
                    <tr>
                        <th>Rol</th>
                        <th>Permiso</th>
                        <th>Ver</th>
                        <th>Crear</th>
                        <th>Editar</th>
                        <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in matriz" :key="`${row.rol}-${row.codigo}`">
                        <td>{{ row.rol }}</td>
                        <td>{{ row.permiso }}</td>
                        <td>{{ row.puede_ver ? 'Sí' : 'No' }}</td>
                        <td>{{ row.puede_crear ? 'Sí' : 'No' }}</td>
                        <td>{{ row.puede_editar ? 'Sí' : 'No' }}</td>
                        <td>{{ row.puede_eliminar ? 'Sí' : 'No' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </VoleyProLayout>
</template>
