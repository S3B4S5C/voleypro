<script setup>
import VoleyProLayout from '@/layouts/VoleyProLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    mode: String,
    resource: String,
    config: Object,
    item: Object,
    options: Object,
});

const initial = {};

Object.keys(props.config.fields).forEach((key) => {
    initial[key] = props.item?.[key] ?? '';
});

const form = useForm(initial);

function submit() {
    if (props.mode === 'create') {
        form.post(`/${props.resource}`);
    } else {
        form.put(`/${props.resource}/${props.item.id}`);
    }
}
</script>

<template>
    <Head
        :title="
            mode === 'create'
                ? `Nuevo ${config.title}`
                : `Editar ${config.title}`
        "
    />

    <VoleyProLayout>
        <div class="vp-page-head">
            <div>
                <h1>
                    {{
                        mode === 'create' ? 'Nuevo registro' : 'Editar registro'
                    }}
                </h1>
                <p>{{ config.title }}</p>
            </div>

            <Link :href="`/${resource}`" class="vp-btn secondary">
                Volver
            </Link>
        </div>

        <form class="vp-card vp-form" @submit.prevent="submit">
            <div
                v-for="(field, key) in config.fields"
                :key="key"
                class="vp-field"
            >
                <label>{{ field.label }}</label>

                <textarea
                    v-if="field.type === 'textarea'"
                    v-model="form[key]"
                    rows="3"
                />

                <select v-else-if="field.type === 'select'" v-model="form[key]">
                    <option value="">Seleccione...</option>
                    <option
                        v-for="option in options[key] || []"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>

                <select
                    v-else-if="field.type === 'select_static'"
                    v-model="form[key]"
                >
                    <option value="">Seleccione...</option>
                    <option
                        v-for="option in field.options"
                        :key="option"
                        :value="option"
                    >
                        {{ option }}
                    </option>
                </select>

                <input
                    v-else-if="field.type === 'decimal'"
                    v-model="form[key]"
                    type="number"
                    step="0.01"
                />

                <input
                    v-else
                    v-model="form[key]"
                    :type="field.type || 'text'"
                />

                <small v-if="form.errors[key]" class="vp-error">
                    {{ form.errors[key] }}
                </small>
            </div>

            <div class="vp-form-actions">
                <button
                    class="vp-btn"
                    type="submit"
                    :disabled="form.processing"
                >
                    Guardar
                </button>
                <Link :href="`/${resource}`" class="vp-btn secondary">
                    Cancelar
                </Link>
            </div>
        </form>
    </VoleyProLayout>
</template>
