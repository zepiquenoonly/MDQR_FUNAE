import './bootstrap'
import './routes'
import '../css/app.css'

import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createI18nInstance } from './i18n/index'

createInertiaApp({
    title: title => `${title} MDQR`,
    resolve: name =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue')
        ),

    setup ({ el, App, props, plugin }) {
        const vueApp = createApp({
            render: () => h(App, props)
        })
        vueApp.use(plugin)

        const i18n = createI18nInstance(props.initialPage)
        vueApp.use(i18n)

        vueApp.mount(el)

        return vueApp
    }
})
