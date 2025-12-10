<template>
    <Layout>
        <div class="p-6">
            <h1 class="text-2xl font-bold text-gray-900">Editar Utilizador</h1>

            <form @submit.prevent="submit" class="mt-6 max-w-lg">
                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nome</label>
                        <input v-model="form.name" type="text" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <div v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</div>
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input v-model="form.email" type="email" id="email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <div v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</div>
                    </div>
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input v-model="form.username" type="text" id="username" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <div v-if="form.errors.username" class="text-sm text-red-600 mt-1">{{ form.errors.username }}</div>
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">Telefone</label>
                        <input v-model="form.phone" type="text" id="phone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        <div v-if="form.errors.phone" class="text-sm text-red-600 mt-1">{{ form.errors.phone }}</div>
                    </div>
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
                        <select v-model="form.role" id="role" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option v-for="role in roles" :key="role" :value="role">{{ role }}</option>
                        </select>
                        <div v-if="form.errors.role" class="text-sm text-red-600 mt-1">{{ form.errors.role }}</div>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="submit" :disabled="form.processing" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Actualizar Utilizador
                    </button>
                </div>
            </form>
        </div>
    </Layout>
</template>

<script setup>
import Layout from '@/Layouts/Layout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    roles: Array,
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    username: props.user.username,
    phone: props.user.phone,
    role: props.user.roles[0]?.name || '',
});

const submit = () => {
    form.patch(route('admin.users.update', props.user.id));
};
</script>
