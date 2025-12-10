# Gestão de Funções e Permissões

Este documento descreve as funções (roles) e permissões (permissions) implementadas no sistema, com foco na nova função de **Administrador do Sistema**.

## Funções Existentes

O sistema possui as seguintes funções principais:

*   **Utente:**
    *   **Descrição:** Utilizador comum que submete reclamações e acompanha o seu estado.
    *   **Permissões:** `submit_complaint`, `view_own_complaints`

*   **Técnico:**
    *   **Descrição:** Responsável por gerir e resolver reclamações atribuídas.
    *   **Permissões:** `manage_complaints`

*   **Gestor:**
    *   **Descrição:** Supervisiona a gestão de reclamações, pode concluir reclamações e aceder a relatórios básicos.
    *   **Permissões:** `manage_complaints`, `conclude_complaints`

*   **Director:**
    *   **Descrição:** Supervisiona a equipa de gestores e técnicos, e tem acesso a relatórios mais abrangentes.
    *   **Permissões:** `manage_complaints`, `conclude_complaints`, `view_reports`

*   **PCA:**
    *   **Descrição:** Acesso a relatórios de alto nível.
    *   **Permissões:** `view_reports`

## Nova Função: Administrador do Sistema (Admin)

Conforme discutido, foi criada uma nova função de **Administrador do Sistema** para centralizar a gestão de entidades chave da plataforma.

*   **Descrição:** Esta é uma função de "Super Admin" com controlo total sobre a configuração e administração do sistema. O Administrador do Sistema é responsável por gerir utilizadores, projetos, departamentos e as próprias funções e permissões.
*   **Responsabilidades Principais:**
    *   Criação, edição e remoção de contas de utilizadores (incluindo PCA, Directores, Gestores, Técnicos e Utentes).
    *   Atribuição e alteração de funções de utilizadores.
    *   Criação, edição e remoção de projetos.
    *   Criação, edição e remoção de departamentos, incluindo a atribuição de gestores de departamento.
    *   Gestão de funções e permissões (futura implementação).
*   **Permissões:** O Administrador do Sistema possui todas as permissões disponíveis no sistema, incluindo:
    *   `submit_complaint`
    *   `view_own_complaints`
    *   `manage_complaints`
    *   `conclude_complaints`
    *   `view_reports`
    *   `manage-users` (Gestão completa de utilizadores)
    *   `manage-projects` (Gestão completa de projetos)
    *   `manage-departments` (Gestão completa de departamentos)
    *   `manage-settings` (Gestão de configurações gerais do sistema)

## Fluxo de Gestão de Entidades

Com a introdução do Administrador do Sistema, o fluxo de gestão de entidades é o seguinte:

*   **Utilizadores:** Apenas o Administrador do Sistema pode criar, editar e remover contas de utilizadores e atribuir-lhes funções.
*   **Projectos:** Apenas o Administrador do Sistema pode criar, editar e remover projetos.
*   **Departamentos:** Apenas o Administrador do Sistema pode criar, editar e remover departamentos e associá-los a gestores.

Esta separação de responsabilidades garante que as tarefas administrativas críticas são centralizadas e controladas por um perfil dedicado, aumentando a segurança e a consistência da gestão da plataforma.
