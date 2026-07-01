<script setup>
import VoleyProLayout from '@/layouts/VoleyProLayout.vue'
import { Head } from '@inertiajs/vue3'

defineProps({
    loginLogs: Array,
    accessLogs: Array,
    searchLogs: Array,
})
</script>

<template>
    <Head title="Bitácora" />

    <VoleyProLayout>
        <div class="vp-page-head">
            <div>
                <h1>Bitácora</h1>
                <p>Login aceptados, fallidos, recursos accedidos y búsquedas.</p>
            </div>
        </div>

        <div class="vp-card">
            <h2>Bitácora de accesos</h2>

            <table class="vp-table">
                <thead>
                <tr>
                    <th>Email intento</th>
                    <th>Tipo</th>
                    <th>IP</th>
                    <th>Descripción</th>
                    <th>Fecha</th>
                </tr>
                </thead>

                <tbody>
                <tr v-for="log in loginLogs" :key="log.id">
                    <td>{{ log.email_intento || '-' }}</td>
                    <td>{{ log.tipo }}</td>
                    <td>{{ log.ip || '-' }}</td>
                    <td>{{ log.descripcion || '-' }}</td>
                    <td>{{ log.fecha }}</td>
                </tr>

                <tr v-if="loginLogs.length === 0">
                    <td colspan="5">No existen registros de acceso.</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="vp-card">
            <h2>Recursos accedidos</h2>

            <table class="vp-table">
                <thead>
                <tr>
                    <th>Ruta</th>
                    <th>Método</th>
                    <th>Acción</th>
                    <th>Fecha</th>
                </tr>
                </thead>

                <tbody>
                <tr v-for="log in accessLogs" :key="log.id">
                    <td>{{ log.ruta }}</td>
                    <td>{{ log.metodo }}</td>
                    <td>{{ log.accion || '-' }}</td>
                    <td>{{ log.fecha }}</td>
                </tr>

                <tr v-if="accessLogs.length === 0">
                    <td colspan="4">No existen recursos registrados.</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="vp-card">
            <h2>Búsquedas</h2>

            <table class="vp-table">
                <thead>
                <tr>
                    <th>Término</th>
                    <th>Módulo</th>
                    <th>Resultados</th>
                    <th>Fecha</th>
                </tr>
                </thead>

                <tbody>
                <tr v-for="log in searchLogs" :key="log.id">
                    <td>{{ log.termino }}</td>
                    <td>{{ log.modulo || '-' }}</td>
                    <td>{{ log.resultados }}</td>
                    <td>{{ log.fecha }}</td>
                </tr>

                <tr v-if="searchLogs.length === 0">
                    <td colspan="4">No existen búsquedas registradas.</td>
                </tr>
                </tbody>
            </table>
        </div>
    </VoleyProLayout>
</template>
