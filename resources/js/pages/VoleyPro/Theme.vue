<script setup>
import VoleyProLayout from '@/layouts/VoleyProLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps({
    temas: Array,
    preferencia: Object,
})

const form = useForm({
    tema_id: props.preferencia?.tema_id || '',
    tamano_fuente: props.preferencia?.tamano_fuente || 'Normal',
    contraste: props.preferencia?.contraste || 'Normal',
    modo_automatico: Boolean(props.preferencia?.modo_automatico ?? true),
})

function submit() {
    form.post('/tema')
}
</script>

<template>
    <Head title="Tema y accesibilidad" />

    <VoleyProLayout>
        <div class="vp-page-head">
            <div>
                <h1>Tema y accesibilidad</h1>
                <p>Permite seleccionar tema para niños, jóvenes, adultos, día/noche y alto contraste.</p>
            </div>
        </div>

        <form class="vp-card vp-form" @submit.prevent="submit">
            <div class="vp-field">
                <label>Tema visual</label>

                <select v-model="form.tema_id">
                    <option value="">Seleccione un tema...</option>

                    <option
                        v-for="tema in temas"
                        :key="tema.id"
                        :value="tema.id"
                    >
                        {{ tema.nombre }} - {{ tema.modo }}
                    </option>
                </select>

                <small v-if="form.errors.tema_id" class="vp-error">
                    {{ form.errors.tema_id }}
                </small>
            </div>

            <div class="vp-field">
                <label>Tamaño de fuente</label>

                <select v-model="form.tamano_fuente">
                    <option value="Pequeño">Pequeño</option>
                    <option value="Normal">Normal</option>
                    <option value="Grande">Grande</option>
                </select>

                <small v-if="form.errors.tamano_fuente" class="vp-error">
                    {{ form.errors.tamano_fuente }}
                </small>
            </div>

            <div class="vp-field">
                <label>Contraste</label>

                <select v-model="form.contraste">
                    <option value="Normal">Normal</option>
                    <option value="Alto">Alto</option>
                </select>

                <small v-if="form.errors.contraste" class="vp-error">
                    {{ form.errors.contraste }}
                </small>
            </div>

            <label class="vp-check">
                <input v-model="form.modo_automatico" type="checkbox" />
                Modo día/noche automático según horario del cliente
            </label>

            <button class="vp-btn" type="submit" :disabled="form.processing">
                Guardar preferencias
            </button>
        </form>
    </VoleyProLayout>
</template>
