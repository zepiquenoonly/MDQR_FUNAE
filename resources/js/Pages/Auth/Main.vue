<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Mobile Layout -->
        <div class="md:hidden w-full h-screen flex flex-col">
            <!-- Login Form (Mobile) -->
            <div v-if="!isRightPanelActive" class="bg-white flex-1 flex flex-col">
                <LoginForm :loading="loading" :errors="errors" @submit="handleLogin"
                    @switch-to-register="switchToRegister" />
            </div>

            <!-- Register Form (Mobile) -->
            <div v-if="isRightPanelActive" class="bg-white flex-1 flex flex-col">
                <RegisterForm :loading="loading" :errors="errors" @submit="handleRegister"
                    @switch-to-login="switchToLogin" />
            </div>

            <!-- Mobile Overlay com altura aumentada -->
            <div class="bg-[#F15F22] text-white flex-shrink-0 h-60">
                <div class="carousel w-full h-full flex flex-col justify-center">
                    <div class="carousel-content relative h-32 flex-1 overflow-hidden">
                        <div class="slides-container relative w-full h-full"
                            :style="{ transform: `translateX(-${currentMobileSlide * 100}%)` }">
                            <!-- Slide 1 -->
                            <div
                                class="slide absolute top-0 left-0 w-full h-full flex flex-col items-center justify-center p-4">
                                <p class="text-base leading-6 text-white text-justify text-center">
                                    Denuncie aqui! É importante que suas reclamações sejam ouvidas e que possamos
                                    tomar as medidas necessárias para resolver os problemas.
                                </p>
                            </div>

                            <!-- Slide 2 -->
                            <div
                                class="slide absolute top-0 left-full w-full h-full flex flex-col items-center justify-center p-4">
                                <p class="text-base leading-6 text-white text-justify text-center">
                                    Percebeu algum problema? Estamos aqui para ouvir você! Denuncie qualquer falha
                                    que tenha identificado. Sua participação é fundamental para corrigirmos
                                    rapidamente e assegurarmos o sucesso e a excelência do projeto.
                                </p>
                            </div>

                            <!-- Slide 3 -->
                            <div
                                class="slide absolute top-0 left-[200%] w-full h-full flex flex-col items-center justify-center p-4">
                                <p class="text-base leading-6 text-white text-justify text-center">
                                    Certifique-se de que sua voz seja ouvida! Este é o espaço ideal para relatar
                                    qualquer falha ou problema identificado no projeto. A sua opinião é fundamental,
                                    e estamos totalmente comprometidos em ouvir suas preocupações e buscar soluções
                                    eficazes. Juntos, podemos melhorar continuamente!
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Dots Indicator Mobile -->
                    <div class="carousel-dots flex justify-center space-x-3 py-4">
                        <button v-for="i in 3" :key="i" @click="setMobileSlide(i - 1)"
                            class="w-3 h-3 rounded-full transition-all duration-300 cursor-pointer"
                            :class="currentMobileSlide === i - 1 ? 'bg-white scale-125' : 'bg-white bg-opacity-50 hover:bg-opacity-75'">
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Desktop Layout -->
        <div class="hidden md:flex min-h-screen bg-gray-100 items-center justify-center p-4">
            <div class="container relative bg-white rounded-lg shadow-2xl overflow-hidden w-full max-w-4xl min-h-[600px] lg:min-h-[650px]"
                :class="{ 'right-panel-active': isRightPanelActive }">

                <!-- Login Form -->
                <div
                    class="form-container sign-in-container absolute top-0 h-full w-1/2 transition-all duration-600 ease-in-out z-20">
                    <LoginForm :loading="loading" :errors="errors" @submit="handleLogin"
                        @switch-to-register="switchToRegister" />
                </div>

                <!-- Register Form -->
                <div
                    class="form-container sign-up-container absolute top-0 h-full w-1/2 transition-all duration-600 ease-in-out z-10 opacity-0 left-0">
                    <RegisterForm :loading="loading" :errors="errors" @submit="handleRegister"
                        @switch-to-login="switchToLogin" />
                </div>

                <!-- Overlay Container -->
                <div
                    class="overlay-container absolute top-0 left-1/2 w-1/2 h-full overflow-hidden transition-transform duration-600 ease-in-out z-30">
                    <div
                        class="overlay relative left-[-100%] h-full w-[200%] transform translate-x-0 
                            transition-transform duration-[600ms] ease-in-out bg-[#F15F22]">

                        <!-- Overlay Left -->
                        <div
                            class="overlay-panel overlay-left absolute flex items-center justify-center flex-col py-0 px-6 lg:px-10 text-center top-0 h-full w-1/2 transform -translate-x-1/5 transition-transform duration-600 ease-in-out">

                            <!-- Carousel para Overlay Left -->
                            <div class="carousel w-full max-w-md">
                                <div class="carousel-content relative h-40 lg:h-48 overflow-hidden">
                                    <div class="slides-container relative w-full h-full"
                                        :style="{ transform: `translateX(-${currentLeftSlide * 100}%)` }">
                                       
                                        <div
                                class="slide absolute top-0 left-0 w-full h-full flex flex-col items-center justify-center p-4">
                                <p class="text-base leading-6 text-white text-justify text-center">
                                    Denuncie aqui! É importante que suas reclamações sejam ouvidas e que possamos
                                    tomar as medidas necessárias para resolver os problemas.
                                </p>
                            </div>

                            <!-- Slide 2 -->
                            <div
                                class="slide absolute top-0 left-full w-full h-full flex flex-col items-center justify-center p-4">
                                <p class="text-base leading-6 text-white text-justify text-center">
                                    Percebeu algum problema? Estamos aqui para ouvir você! Denuncie qualquer falha
                                    que tenha identificado. Sua participação é fundamental para corrigirmos
                                    rapidamente e assegurarmos o sucesso e a excelência do projeto.
                                </p>
                            </div>

                            <!-- Slide 3 -->
                            <div
                                class="slide absolute top-0 left-[200%] w-full h-full flex flex-col items-center justify-center p-4">
                                <p class="text-base leading-6 text-white text-justify text-center">
                                    Certifique-se de que sua voz seja ouvida! Este é o espaço ideal para relatar
                                    qualquer falha ou problema identificado no projeto. A sua opinião é fundamental,
                                    e estamos totalmente comprometidos em ouvir suas preocupações e buscar soluções
                                    eficazes. Juntos, podemos melhorar continuamente!
                                </p>
                            </div>
                                    </div>
                                </div>

                                <!-- Dots Indicator Left -->
                                <div class="carousel-dots flex justify-center space-x-3 mt-4 lg:mt-6">
                                    <button v-for="i in 3" :key="i" @click="setLeftSlide(i - 1)"
                                        class="w-2 h-2 lg:w-3 lg:h-3 rounded-full transition-all duration-300 cursor-pointer"
                                        :class="currentLeftSlide === i - 1 ? 'bg-white scale-125' : 'bg-white bg-opacity-50 hover:bg-opacity-75'">
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Overlay Right -->
                        <div
                            class="overlay-panel overlay-right absolute flex items-center justify-center flex-col py-0 px-6 lg:px-10 text-center top-0 h-full w-1/2 right-0 transform translate-x-0 transition-transform duration-600 ease-in-out">

                            <!-- Carousel para Overlay Right -->
                            <div class="carousel w-full max-w-md">
                                <div class="carousel-content relative h-40 lg:h-48 overflow-hidden">
                                    <div class="slides-container relative w-full h-full"
                                        :style="{ transform: `translateX(-${currentRightSlide * 100}%)` }">
                                        <div
                                class="slide absolute top-0 left-0 w-full h-full flex flex-col items-center justify-center p-4">
                                <p class="text-base leading-6 text-white text-justify text-center">
                                    Denuncie aqui! É importante que suas reclamações sejam ouvidas e que possamos
                                    tomar as medidas necessárias para resolver os problemas.
                                </p>
                            </div>

                            <!-- Slide 2 -->
                            <div
                                class="slide absolute top-0 left-full w-full h-full flex flex-col items-center justify-center p-4">
                                <p class="text-base leading-6 text-white text-justify text-center">
                                    Percebeu algum problema? Estamos aqui para ouvir você! Denuncie qualquer falha
                                    que tenha identificado. Sua participação é fundamental para corrigirmos
                                    rapidamente e assegurarmos o sucesso e a excelência do projeto.
                                </p>
                            </div>

                            <!-- Slide 3 -->
                            <div
                                class="slide absolute top-0 left-[200%] w-full h-full flex flex-col items-center justify-center p-4">
                                <p class="text-base leading-6 text-white text-justify text-center">
                                    Certifique-se de que sua voz seja ouvida! Este é o espaço ideal para relatar
                                    qualquer falha ou problema identificado no projeto. A sua opinião é fundamental,
                                    e estamos totalmente comprometidos em ouvir suas preocupações e buscar soluções
                                    eficazes. Juntos, podemos melhorar continuamente!
                                </p>
                            </div>
                                    </div>
                                </div>

                                <!-- Dots Indicator Right -->
                                <div class="carousel-dots flex justify-center space-x-3 mt-4 lg:mt-6">
                                    <button v-for="i in 3" :key="i" @click="setRightSlide(i - 1)"
                                        class="w-2 h-2 lg:w-3 lg:h-3 rounded-full transition-all duration-300 cursor-pointer"
                                        :class="currentRightSlide === i - 1 ? 'bg-white scale-125' : 'bg-white bg-opacity-50 hover:bg-opacity-75'">
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import LoginForm from '../../Components/Authenticate/LoginForm.vue'
import RegisterForm from '../../Components/Authenticate/RegisterForm.vue'

const page = usePage()
const isRightPanelActive = ref(page.props.initialPanel === 'register')
const loading = ref(false)
const currentLeftSlide = ref(0)
const currentRightSlide = ref(0)
const currentMobileSlide = ref(0)


const errors = computed(() => {
    return usePage().props.errors || {}
})


let leftSlideInterval
let rightSlideInterval
let mobileSlideInterval

const startSlideRotation = () => {
    leftSlideInterval = setInterval(() => {
        currentLeftSlide.value = (currentLeftSlide.value + 1) % 3
    }, 10000)

    rightSlideInterval = setInterval(() => {
        currentRightSlide.value = (currentRightSlide.value + 1) % 3
    }, 10000)

    mobileSlideInterval = setInterval(() => {
        currentMobileSlide.value = (currentMobileSlide.value + 1) % 3
    }, 10000)
}

const stopSlideRotation = () => {
    if (leftSlideInterval) clearInterval(leftSlideInterval)
    if (rightSlideInterval) clearInterval(rightSlideInterval)
    if (mobileSlideInterval) clearInterval(mobileSlideInterval)
}


const setLeftSlide = (index) => {
    currentLeftSlide.value = index
    resetLeftInterval()
}

const setRightSlide = (index) => {
    currentRightSlide.value = index
    resetRightInterval()
}

const setMobileSlide = (index) => {
    currentMobileSlide.value = index
    resetMobileInterval()
}

const resetLeftInterval = () => {
    if (leftSlideInterval) clearInterval(leftSlideInterval)
    leftSlideInterval = setInterval(() => {
        currentLeftSlide.value = (currentLeftSlide.value + 1) % 3
    }, 10000)
}

const resetRightInterval = () => {
    if (rightSlideInterval) clearInterval(rightSlideInterval)
    rightSlideInterval = setInterval(() => {
        currentRightSlide.value = (currentRightSlide.value + 1) % 3
    }, 10000)
}

const resetMobileInterval = () => {
    if (mobileSlideInterval) clearInterval(mobileSlideInterval)
    mobileSlideInterval = setInterval(() => {
        currentMobileSlide.value = (currentMobileSlide.value + 1) % 3
    }, 10000)
}


const switchToRegister = () => {
    router.get('/register')
}

const switchToLogin = () => {
    router.get('/login')
}

const handleRegister = async (e) => {
    loading.value = true
    const form = new FormData(e.target)
    const data = {
        name: form.get('name'),
        email: form.get('email'),
        password: form.get('password'),
        password_confirmation: form.get('password_confirmation')
    }

    try {
        await router.post('/register', data)
    } catch (err) {
        console.error("Erro de cadastro:", err)
    } finally {
        loading.value = false
    }
}

const handleLogin = async (e) => {
    loading.value = true
    const form = new FormData(e.target)
    const data = {
        email: form.get('email'),
        password: form.get('password'),
        remember: form.get('remember') === 'on'
    }

    try {
        await router.post('/login', data)
    } catch (err) {
        console.error("Erro de login:", err)
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    startSlideRotation()
})

onUnmounted(() => {
    stopSlideRotation()
})
</script>

<style scoped>
.container.right-panel-active .sign-in-container {
    transform: translateX(100%);
}

.container.right-panel-active .sign-up-container {
    transform: translateX(100%);
    opacity: 1;
    z-index: 25;
    animation: show 0.6s;
}

@keyframes show {

    0%,
    49.99% {
        opacity: 0;
        z-index: 1;
    }

    50%,
    100% {
        opacity: 1;
        z-index: 25;
    }
}

.container.right-panel-active .overlay-container {
    transform: translateX(-100%);
}

.container.right-panel-active .overlay {
    transform: translateX(50%);
}

.container.right-panel-active .overlay-left {
    transform: translateX(0);
}

.container.right-panel-active .overlay-right {
    transform: translateX(20%);
}

.slides-container {
    display: flex;
    transition: transform 0.5s ease-in-out;
    width: 300%; 
}

.slide {
    width: 33.333%;
    flex-shrink: 0;
}

.carousel-content {
    perspective: 1000px;
}
</style>