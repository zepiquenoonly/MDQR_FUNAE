<template>
    <Layout>
        <div class="p-6">
            <h1 class="text-2xl font-bold text-gray-900">Novo Projecto</h1>

            <form @submit.prevent="submit" class="mt-6 space-y-6">
                <!-- Project Details -->
                <div class="p-4 border rounded-md">
                    <h2 class="text-lg font-medium text-gray-900">Detalhes do Projecto</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Nome</label>
                            <input v-model="form.name" type="text" id="name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700">Categoria</label>
                            <select v-model="form.category" id="category" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="andamento">Em Andamento</option>
                                <option value="parados">Parados</option>
                                <option value="finalizados">Finalizados</option>
                            </select>
                        </div>
                        <div>
                            <label for="provincia" class="block text-sm font-medium text-gray-700">Província</label>
                            <input v-model="form.provincia" type="text" id="provincia" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="distrito" class="block text-sm font-medium text-gray-700">Distrito</label>
                            <input v-model="form.distrito" type="text" id="distrito" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="bairro" class="block text-sm font-medium text-gray-700">Bairro</label>
                            <input v-model="form.bairro" type="text" id="bairro" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="data_criacao" class="block text-sm font-medium text-gray-700">Data de Criação</label>
                            <input v-model="form.data_criacao" type="date" id="data_criacao" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div class="col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-700">Descrição</label>
                            <textarea v-model="form.description" id="description" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        </div>
                        <div>
                            <label for="image" class="block text-sm font-medium text-gray-700">Imagem</label>
                            <input @change="onFileChange" type="file" id="image" class="mt-1">
                        </div>
                    </div>
                </div>

                <!-- Objectives -->
                <div class="p-4 border rounded-md">
                    <h2 class="text-lg font-medium text-gray-900">Objectivos</h2>
                    <div v-for="(objective, index) in form.objectives" :key="index" class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4 border-t pt-4">
                        <div>
                            <label :for="`objective_title_${index}`" class="block text-sm font-medium text-gray-700">Título</label>
                            <input v-model="objective.title" type="text" :id="`objective_title_${index}`" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div class="col-span-2">
                            <label :for="`objective_description_${index}`" class="block text-sm font-medium text-gray-700">Descrição</label>
                            <textarea v-model="objective.description" :id="`objective_description_${index}`" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        </div>
                        <button @click.prevent="removeObjective(index)" class="text-red-500">Remover Objectivo</button>
                    </div>
                    <button @click.prevent="addObjective" class="mt-4 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded">Adicionar Objectivo</button>
                </div>

                <!-- Finance -->
                <div class="p-4 border rounded-md">
                    <h2 class="text-lg font-medium text-gray-900">Financiamento</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div>
                            <label for="financiador" class="block text-sm font-medium text-gray-700">Financiador</label>
                            <input v-model="form.financiador" type="text" id="financiador" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="beneficiario" class="block text-sm font-medium text-gray-700">Beneficiário</label>
                            <input v-model="form.beneficiario" type="text" id="beneficiario" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="responsavel" class="block text-sm font-medium text-gray-700">Responsável</label>
                            <input v-model="form.responsavel" type="text" id="responsavel" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="valor_financiado" class="block text-sm font-medium text-gray-700">Valor Financiado</label>
                            <input v-model="form.valor_financiado" type="text" id="valor_financiado" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="codigo" class="block text-sm font-medium text-gray-700">Código</label>
                            <input v-model="form.codigo" type="text" id="codigo" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>
                </div>

                <!-- Deadlines -->
                <div class="p-4 border rounded-md">
                     <h2 class="text-lg font-medium text-gray-900">Prazos</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                        <div>
                            <label for="data_aprovacao" class="block text-sm font-medium text-gray-700">Data de Aprovação</label>
                            <input v-model="form.data_aprovacao" type="date" id="data_aprovacao" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="data_inicio" class="block text-sm font-medium text-gray-700">Data de Início</label>
                            <input v-model="form.data_inicio" type="date" id="data_inicio" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="data_inspecao" class="block text-sm font-medium text-gray-700">Data de Inspecção</label>
                            <input v-model="form.data_inspecao" type="date" id="data_inspecao" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="data_finalizacao" class="block text-sm font-medium text-gray-700">Data de Finalização</label>
                            <input v-model="form.data_finalizacao" type="date" id="data_finalizacao" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="data_inauguracao" class="block text-sm font-medium text-gray-700">Data de Inauguração</label>
                            <input v-model="form.data_inauguracao" type="date" id="data_inauguracao" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Criar Projecto
                    </button>
                </div>
            </form>
        </div>
    </Layout>
</template>

<script setup>
import Layout from '@/Layouts/Layout.vue';
import { ref } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import axios from 'axios';

const form = ref({
    name: '',
    description: '',
    image: null,
    provincia: '',
    distrito: '',
    bairro: '',
    category: 'andamento',
    data_criacao: '',
    objectives: [],
    financiador: '',
    beneficiario: '',
    responsavel: '',
    valor_financiado: '',
    codigo: '',
    data_aprovacao: '',
    data_inicio: '',
    data_inspecao: '',
    data_finalizacao: '',
    data_inauguracao: '',
});

function onFileChange(e) {
    form.value.image = e.target.files[0];
}

function addObjective() {
    form.value.objectives.push({ title: '', description: '' });
}

function removeObjective(index) {
    form.value.objectives.splice(index, 1);
}

async function submit() {
    const formData = new FormData();
    for (const key in form.value) {
        if (key === 'objectives') {
            form.value.objectives.forEach((objective, index) => {
                formData.append(`objectives[${index}][title]`, objective.title);
                formData.append(`objectives[${index}][description]`, objective.description);
            });
        } else {
            formData.append(key, form.value[key]);
        }
    }

    try {
        await axios.post('/api/projects', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
        Inertia.visit('/admin/projects');
    } catch (error) {
        console.error('Erro ao criar projecto:', error.response.data);
    }
}
</script>
