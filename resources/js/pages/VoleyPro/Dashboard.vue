<script setup>
    import VoleyProLayout from '@/layouts/VoleyProLayout.vue'
    import { Head } from '@inertiajs/vue3'

    defineProps({
        stats: Object,
        ultimosPagos: Array,
        paginasMasVisitadas: Array,
    })
</script>

<template>
    <Head title="Dashboard" />

    <VoleyProLayout>
        <div class="vp-page-head">
            <div>
                <h1>Dashboard VoleyPro</h1>
                <p>Estadísticas del negocio y accesos del sitio.</p>
            </div>
        </div>

        <div class="vp-grid">
            <div class="vp-stat"><span>Alumnos</span><strong>{{ stats.alumnos }}</strong></div>
            <div class="vp-stat"><span>Entrenadores</span><strong>{{ stats.entrenadores }}</strong></div>
            <div class="vp-stat"><span>Grupos</span><strong>{{ stats.grupos }}</strong></div>
            <div class="vp-stat"><span>Inscripciones activas</span><strong>{{ stats.inscripciones }}</strong></div>
            <div class="vp-stat"><span>Pagos pendientes</span><strong>{{ stats.pagosPendientes }}</strong></div>
            <div class="vp-stat"><span>Pagos confirmados</span><strong>{{ stats.pagosConfirmados }}</strong></div>
            <div class="vp-stat"><span>Total cobrado</span><strong>{{ stats.totalCobrado }} Bs</strong></div>
            <div class="vp-stat"><span>Visitas totales</span><strong>{{ stats.visitas }}</strong></div>
        </div>

        <div class="vp-two-cols">
            <div class="vp-card">
                <h2>Últimos pagos</h2>
                <table class="vp-table">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Monto</th>
                        <th>Estado</th>
                        <th>Referencia</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="pago in ultimosPagos" :key="pago.id">
                        <td>{{ pago.id }}</td>
                        <td>{{ pago.monto }} Bs</td>
                        <td>{{ pago.estado }}</td>
                        <td>{{ pago.referencia || '-' }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="vp-card">
                <h2>Páginas más visitadas</h2>
                <table class="vp-table">
                    <thead>
                    <tr>
                        <th>Ruta</th>
                        <th>Visitas</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="pagina in paginasMasVisitadas" :key="pagina.id">
                        <td>{{ pagina.ruta }}</td>
                        <td>{{ pagina.contador }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </VoleyProLayout>
</template>
