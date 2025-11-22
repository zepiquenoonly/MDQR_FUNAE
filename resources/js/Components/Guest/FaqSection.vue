<template>
    <section id="faq" class="py-20 px-4 sm:px-6 lg:px-8 bg-white">
        <div class="max-w-4xl mx-auto">
            <h2 class="text-4xl font-bold text-center text-brand mb-16">PERGUNTAS FREQUENTES</h2>

            <div class="space-y-4">
                <FaqItem v-for="(faq, index) in faqs" :key="index" :question="faq.question" :answer="faq.answer" />
            </div>
        </div>
    </section>
</template>

<script setup>
import FaqItem from './FaqItem.vue'

const faqs = [
    {
        question: 'Como posso fazer uma denúncia na plataforma?',
        answer: 'Para fazer uma denúncia, basta acessar o sistema, criar ou entrar no seu perfil e selecionar o projeto relacionado à denúncia. Depois, preencha o formulário com os detalhes necessários e envie sua solicitação.'
    },
    {
        question: 'É possível acompanhar o status da minha reclamação?',
        answer: 'Sim! Após submeter sua reclamação, você pode acompanhar o progresso através de uma linha do tempo que mostra o status atual, como "recebida", "em análise" ou "resolvida".'
    },
    {
        question: 'As sugestões enviadas pelos munícipes são consideradas?',
        answer: 'Sim, todas as sugestões são analisadas pela equipe responsável e, quando viáveis, são incorporadas aos projetos municipais.'
    },
    {
        question: 'Como posso enviar documentos ou provas anexas?',
        answer: 'Durante o preenchimento do formulário, você pode anexar documentos, fotos ou outros arquivos relevantes para apoiar sua denúncia ou sugestão.'
    },
    {
        question: 'Quem tem acesso às denúncias enviadas?',
        answer: 'Apenas os gestores e técnicos autorizados têm acesso às denúncias para garantir a privacidade e segurança das informações.'
    },
    {
        question: 'Como posso alterar as informações do meu perfil?',
        answer: 'Você pode editar suas informações pessoais acessando a seção de perfil no sistema após fazer login.'
    }
]
</script>