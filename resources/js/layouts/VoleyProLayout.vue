<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref } from 'vue'

const page = usePage()

const search = ref('')

const voleypro = computed(() => page.props.voleypro || {})
const menu = computed(() => voleypro.value.menu || [])
const theme = computed(() => voleypro.value.theme || { class: 'theme-youth', fontSize: 'normal', auto: true })

const themeClass = computed(() => {
    if (theme.value.contrast) {
        return 'theme-contrast'
    }

    if (theme.value.auto) {
        const hour = new Date().getHours()
        return hour >= 7 && hour < 19 ? 'theme-day' : 'theme-night'
    }

    return theme.value.class || 'theme-youth'
})

function buscar() {
    if (!search.value.trim()) return
    router.get('/buscar', { q: search.value })
}

function logout() {
    router.post('/logout')
}
</script>

<template>
    <div :class="['vp-app', themeClass, `font-${theme.fontSize || 'normal'}`]">
        <aside class="vp-sidebar">
            <div class="vp-brand">
                <div class="vp-logo">VP</div>
                <div>
                    <strong>VoleyPro</strong>
                    <small>Escuela de vóley</small>
                </div>
            </div>

            <nav class="vp-menu">
                <Link
                    v-for="item in menu"
                    :key="item.id"
                    :href="item.ruta"
                    class="vp-menu-item"
                >
                    {{ item.nombre }}
                </Link>
            </nav>
        </aside>

        <main class="vp-main">
            <header class="vp-header">
                <form class="vp-search" @submit.prevent="buscar">
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Buscar alumnos, pagos, ofertas..."
                    />
                    <button type="submit">Buscar</button>
                </form>

                <div class="vp-header-actions">
                    <Link href="/tema" class="vp-btn secondary">Tema</Link>
                    <button class="vp-btn danger" type="button" @click="logout">Salir</button>
                </div>
            </header>

            <section class="vp-content">
                <div v-if="$page.props.flash?.success" class="vp-alert success">
                    {{ $page.props.flash.success }}
                </div>

                <div v-if="$page.props.flash?.error" class="vp-alert danger">
                    {{ $page.props.flash.error }}
                </div>

                <slot />
            </section>

            <footer class="vp-footer">
                <span>VoleyPro - MVC/MVVM Laravel + Inertia + Vue</span>
                <span>Visitas de esta página: {{ voleypro.pageVisits ?? 0 }}</span>
            </footer>
        </main>
    </div>
</template>
