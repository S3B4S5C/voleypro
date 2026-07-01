<script setup>
import VoleyProLayout from '@/layouts/VoleyProLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    resource: String,
    config: Object,
    items: Object,
    filters: Object,
    can: Object,
});

const page = usePage();
const q = ref(props.filters?.q || '');

function buscar() {
    router.get(`/${props.resource}`, { q: q.value }, { preserveState: true });
}

function eliminar(id) {
    if (
        !confirm(
            'No se eliminará físicamente. El registro será desactivado/cancelado. ¿Continuar?',
        )
    )
        return;
    router.delete(`/${props.resource}/${id}`);
}

function generarPagoFacil(id) {
    router.post(`/pagos/${id}/pago-facil/generar`);
}

function verificarPago(id) {
    router.post(`/pagos/${id}/pago-facil/verificar`);
}

function confirmarDemo(id) {
    if (!confirm('Esto marcará el pago como Pagado en modo demo. ¿Continuar?'))
        return;
    router.post(`/pagos/${id}/pago-facil/confirmar-demo`);
}

function qrSrc(qr) {
    if (!qr) return '';
    if (String(qr).startsWith('data:')) return qr;
    return `data:image/png;base64,${qr}`;
}
</script>

<template>
    <Head :title="config.title" />

    <VoleyProLayout>
        <div class="vp-page-head">
            <div>
                <h1>{{ config.title }}</h1>
                <p>Gestión de registros de {{ config.title.toLowerCase() }}.</p>
            </div>

            <Link v-if="can.crear" :href="`/${resource}/crear`" class="vp-btn">
                Nuevo
            </Link>
        </div>

        <div v-if="page.props.errors?.general" class="vp-alert danger">
            {{ page.props.errors.general }}
        </div>

        <div v-if="page.props.errors?.pagofacil" class="vp-alert danger">
            {{ page.props.errors.pagofacil }}
        </div>

        <div class="vp-card">
            <form class="vp-toolbar" @submit.prevent="buscar">
                <input v-model="q" type="text" placeholder="Buscar..." />
                <button class="vp-btn" type="submit">Buscar</button>
            </form>

            <div class="vp-table-wrap">
                <table class="vp-table">
                    <thead>
                        <tr>
                            <th
                                v-for="(label, key) in config.columns"
                                :key="key"
                            >
                                {{ label }}
                            </th>

                            <th v-if="resource === 'pagos'">QR / Enlaces</th>

                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-for="item in items.data" :key="item.id">
                            <td
                                v-for="(label, key) in config.columns"
                                :key="key"
                            >
                                {{ item[key] ?? '-' }}
                            </td>

                            <td v-if="resource === 'pagos'">
                                <div v-if="item.qr_base64" class="vp-qr-box">
                                    <img
                                        :src="qrSrc(item.qr_base64)"
                                        alt="QR PagoFácil"
                                        class="vp-qr"
                                    />
                                </div>

                                <div class="vp-links">
                                    <a
                                        v-if="item.checkout_url"
                                        :href="item.checkout_url"
                                        target="_blank"
                                    >
                                        Checkout
                                    </a>

                                    <a
                                        v-if="item.universal_url"
                                        :href="item.universal_url"
                                        target="_blank"
                                    >
                                        Universal URL
                                    </a>

                                    <a
                                        v-if="item.qr_content_url"
                                        :href="item.qr_content_url"
                                        target="_blank"
                                    >
                                        QR URL
                                    </a>
                                </div>

                                <small v-if="item.expiration_date">
                                    Expira: {{ item.expiration_date }}
                                </small>
                            </td>

                            <td class="vp-actions">
                                <Link
                                    v-if="can.editar"
                                    :href="`/${resource}/${item.id}/editar`"
                                    class="vp-btn small secondary"
                                >
                                    Editar
                                </Link>

                                <button
                                    v-if="can.eliminar"
                                    class="vp-btn small danger"
                                    type="button"
                                    @click="eliminar(item.id)"
                                >
                                    Desactivar
                                </button>

                                <template v-if="resource === 'pagos'">
                                    <button
                                        v-if="can.ver"
                                        class="vp-btn small"
                                        type="button"
                                        @click="generarPagoFacil(item.id)"
                                    >
                                        Generar QR
                                    </button>

                                    <button
                                        v-if="can.ver"
                                        class="vp-btn small secondary"
                                        type="button"
                                        @click="verificarPago(item.id)"
                                    >
                                        Verificar
                                    </button>

                                    <button
                                        v-if="can.editar"
                                        class="vp-btn small success"
                                        type="button"
                                        @click="confirmarDemo(item.id)"
                                    >
                                        Demo pagado
                                    </button>
                                </template>
                            </td>
                        </tr>

                        <tr v-if="items.data.length === 0">
                            <td
                                :colspan="
                                    Object.keys(config.columns).length +
                                    (resource === 'pagos' ? 2 : 1)
                                "
                            >
                                No existen registros.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="vp-pagination">
                <Link
                    v-for="link in items.links"
                    :key="link.label"
                    :href="link.url || '#'"
                    v-html="link.label"
                    :class="{ active: link.active, disabled: !link.url }"
                />
            </div>
        </div>
    </VoleyProLayout>
</template>
