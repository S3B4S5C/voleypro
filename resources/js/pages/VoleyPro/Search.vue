<script setup>
import VoleyProLayout from '@/layouts/VoleyProLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    q: String,
    results: Object,
});
</script>

<template>
    <Head title="Búsqueda" />

    <VoleyProLayout>
        <div class="vp-page-head">
            <div>
                <h1>Resultados de búsqueda</h1>
                <p>Término buscado: "{{ q }}"</p>
            </div>
        </div>

        <div class="vp-card">
            <div
                v-for="(rows, module) in results"
                :key="module"
                class="vp-search-section"
            >
                <h2>{{ module }}</h2>

                <div v-if="rows.length === 0">Sin resultados.</div>

                <ul v-else>
                    <li v-for="row in rows" :key="row.id">
                        #{{ row.id }}
                        {{
                            row.nombre ||
                            row.payment_number ||
                            row.estado ||
                            '-'
                        }}
                        {{ row.apellido || '' }}
                        <span v-if="row.email"> - {{ row.email }}</span>
                        <span v-if="row.gestion"> - {{ row.gestion }}</span>
                        <span v-if="row.monto"> - {{ row.monto }} Bs</span>
                    </li>
                </ul>
            </div>
        </div>
    </VoleyProLayout>
</template>
