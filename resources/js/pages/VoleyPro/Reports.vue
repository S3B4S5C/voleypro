<script setup>
import VoleyProLayout from '@/layouts/VoleyProLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    filters: Object,
    pagosPorEstado: Array,
    alumnosPorEstado: Array,
    inscripcionesPorEstado: Array,
    recursosMasAccedidos: Array,
    busquedasFrecuentes: Array,
});

const desde = ref(props.filters?.desde || '');
const hasta = ref(props.filters?.hasta || '');

function filtrar() {
    router.get(
        '/reportes',
        { desde: desde.value, hasta: hasta.value },
        { preserveState: true },
    );
}

function exportar(tipo) {
    const params = new URLSearchParams({
        tipo,
        desde: desde.value || '',
        hasta: hasta.value || '',
    });

    window.location.href = `/reportes/exportar?${params.toString()}`;
}

function maxTotal(rows) {
    if (!rows || rows.length === 0) return 1;
    return Math.max(...rows.map((r) => Number(r.total || 0)), 1);
}
</script>

<template>
    <Head title="Reportes" />

    <VoleyProLayout>
        <div class="vp-page-head">
            <div>
                <h1>Reportes y estadísticas</h1>
                <p>Filtros, gráficos simples y exportación CSV para defensa.</p>
            </div>
        </div>

        <div class="vp-card">
            <form class="vp-toolbar" @submit.prevent="filtrar">
                <input v-model="desde" type="date" />
                <input v-model="hasta" type="date" />
                <button class="vp-btn" type="submit">Filtrar</button>
                <button
                    class="vp-btn secondary"
                    type="button"
                    @click="exportar('pagos')"
                >
                    Exportar pagos CSV
                </button>
                <button
                    class="vp-btn secondary"
                    type="button"
                    @click="exportar('accesos')"
                >
                    Exportar accesos CSV
                </button>
            </form>
        </div>

        <div class="vp-two-cols">
            <div class="vp-card">
                <h2>Pagos por estado</h2>

                <div class="vp-chart">
                    <div
                        v-for="row in pagosPorEstado"
                        :key="row.estado"
                        class="vp-bar-row"
                    >
                        <span>{{ row.estado }}</span>
                        <div class="vp-bar">
                            <div
                                :style="{
                                    width: `${(Number(row.total || 0) / maxTotal(pagosPorEstado)) * 100}%`,
                                }"
                            ></div>
                        </div>
                        <strong>{{ row.total }}</strong>
                    </div>
                </div>

                <table class="vp-table">
                    <thead>
                        <tr>
                            <th>Estado</th>
                            <th>Total</th>
                            <th>Monto</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="row in pagosPorEstado" :key="row.estado">
                            <td>{{ row.estado }}</td>
                            <td>{{ row.total }}</td>
                            <td>{{ row.monto || 0 }} Bs</td>
                        </tr>

                        <tr v-if="pagosPorEstado.length === 0">
                            <td colspan="3">No existen pagos registrados.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="vp-card">
                <h2>Recursos más accedidos</h2>

                <div class="vp-chart">
                    <div
                        v-for="row in recursosMasAccedidos"
                        :key="row.ruta"
                        class="vp-bar-row"
                    >
                        <span>{{ row.ruta }}</span>
                        <div class="vp-bar">
                            <div
                                :style="{
                                    width: `${(Number(row.total || 0) / maxTotal(recursosMasAccedidos)) * 100}%`,
                                }"
                            ></div>
                        </div>
                        <strong>{{ row.total }}</strong>
                    </div>
                </div>

                <table class="vp-table">
                    <thead>
                        <tr>
                            <th>Ruta</th>
                            <th>Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="row in recursosMasAccedidos" :key="row.ruta">
                            <td>{{ row.ruta }}</td>
                            <td>{{ row.total }}</td>
                        </tr>

                        <tr v-if="recursosMasAccedidos.length === 0">
                            <td colspan="2">No existen accesos registrados.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="vp-card">
                <h2>Alumnos por estado</h2>

                <table class="vp-table">
                    <thead>
                        <tr>
                            <th>Estado</th>
                            <th>Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="row in alumnosPorEstado" :key="row.estado">
                            <td>{{ row.estado }}</td>
                            <td>{{ row.total }}</td>
                        </tr>

                        <tr v-if="alumnosPorEstado.length === 0">
                            <td colspan="2">No existen alumnos registrados.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="vp-card">
                <h2>Búsquedas frecuentes</h2>

                <table class="vp-table">
                    <thead>
                        <tr>
                            <th>Término</th>
                            <th>Total</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="row in busquedasFrecuentes"
                            :key="row.termino"
                        >
                            <td>{{ row.termino }}</td>
                            <td>{{ row.total }}</td>
                        </tr>

                        <tr v-if="busquedasFrecuentes.length === 0">
                            <td colspan="2">
                                No existen búsquedas registradas.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </VoleyProLayout>
</template>
