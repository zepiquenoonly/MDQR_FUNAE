<template>
    <section id="inicio" class="bg-brand pt-32 pb-20 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
            <!-- Content -->
            <div class="text-white">
                <p class="text-sm uppercase tracking-wider mb-4 opacity-90">FAÇA SUA VOZ SER OUVIDA!</p>
                <h1 class="text-3xl md:text-4xl lg:text-5xl xl:text-6xl font-bold mb-8 leading-tight">
                    RECLAME, SUGIRA, QUEIXA!
                </h1>
                <p class="text-lg md:text-xl mb-10 leading-relaxed opacity-95">
                    Estamos aqui para acolher as suas ideias e resolver problemas. Participe e ajude a construir um
                    futuro melhor!
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <a :href="authRoutes.register"
                        class="bg-white text-black hover:bg-gray-100 hover:text-brand px-8 py-4 rounded-full text-lg font-bold inline-block transition-all hover:shadow-xl hover:-translate-y-1 text-center">
                        CRIA UMA CONTA
                    </a>
                    <a href="/grievances-home"
                        class="bg-white text-black hover:bg-gray-100 hover:text-brand px-8 py-4 rounded-full text-lg font-bold inline-block transition-all hover:shadow-xl hover:-translate-y-1 text-center">
                        RECLAME AQUI
                    </a>
                </div>
            </div>

            <!-- Image -->
            <div class="rounded-2xl overflow-hidden shadow-2xl bg-gray-300 flex items-center justify-center min-h-96">
                <img src="/images/yellow-excavator-construction-site-against-blue-sky-heavy-industry.jpg"
                    alt="Ícone de autenticação" />
            </div>
        </div>
    </section>
</template>

<script setup>
defineProps({
    authRoutes: {
        type: Object,
        required: true,
        default: () => ({
            login: '#',
            register: '#'
        })
    }
})
</script>
