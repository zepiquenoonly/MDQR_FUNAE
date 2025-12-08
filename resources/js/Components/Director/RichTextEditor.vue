<!-- @/Components/Common/RichTextEditor.vue -->
<template>
  <div class="rich-text-editor">
    <!-- Barra de ferramentas -->
    <div
      class="toolbar border border-gray-300 dark:border-gray-600 rounded-t-lg bg-gray-50 dark:bg-gray-800 p-2 flex flex-wrap items-center gap-1"
    >
      <!-- Estilos de texto -->
      <button
        type="button"
        @click="formatText('bold')"
        :class="[
          'p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700',
          activeFormats.bold ? 'bg-gray-300 dark:bg-gray-600' : '',
        ]"
        title="Negrito"
      >
        <BoldIcon class="h-4 w-4" />
      </button>
      <button
        type="button"
        @click="formatText('italic')"
        :class="[
          'p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700',
          activeFormats.italic ? 'bg-gray-300 dark:bg-gray-600' : '',
        ]"
        title="Itálico"
      >
        <ItalicIcon class="h-4 w-4" />
      </button>
      <button
        type="button"
        @click="formatText('underline')"
        :class="[
          'p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700',
          activeFormats.underline ? 'bg-gray-300 dark:bg-gray-600' : '',
        ]"
        title="Sublinhado"
      >
        <UnderlineIcon class="h-4 w-4" />
      </button>

      <div class="h-4 w-px bg-gray-300 dark:bg-gray-600 mx-1"></div>

      <!-- Listas -->
      <button
        type="button"
        @click="formatText('insertUnorderedList')"
        :class="[
          'p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700',
          activeFormats.ul ? 'bg-gray-300 dark:bg-gray-600' : '',
        ]"
        title="Lista não ordenada"
      >
        <ListBulletIcon class="h-4 w-4" />
      </button>
      <button
        type="button"
        @click="formatText('insertOrderedList')"
        :class="[
          'p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700',
          activeFormats.ol ? 'bg-gray-300 dark:bg-gray-600' : '',
        ]"
        title="Lista ordenada"
      >
        <Bars4Icon class="h-4 w-4" />
      </button>

      <div class="h-4 w-px bg-gray-300 dark:bg-gray-600 mx-1"></div>

      <!-- Alinhamento -->
      <button
        type="button"
        @click="formatText('justifyLeft')"
        :class="[
          'p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700',
          activeFormats.alignLeft ? 'bg-gray-300 dark:bg-gray-600' : '',
        ]"
        title="Alinhar à esquerda"
      >
        <Bars3BottomLeftIcon class="h-4 w-4" />
      </button>
      <button
        type="button"
        @click="formatText('justifyCenter')"
        :class="[
          'p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700',
          activeFormats.alignCenter ? 'bg-gray-300 dark:bg-gray-600' : '',
        ]"
        title="Alinhar ao centro"
      >
        <Bars3Icon class="h-4 w-4" />
      </button>
      <button
        type="button"
        @click="formatText('justifyRight')"
        :class="[
          'p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700',
          activeFormats.alignRight ? 'bg-gray-300 dark:bg-gray-600' : '',
        ]"
        title="Alinhar à direita"
      >
        <Bars3BottomRightIcon class="h-4 w-4" />
      </button>

      <div class="h-4 w-px bg-gray-300 dark:bg-gray-600 mx-1"></div>

      <!-- Links -->
      <button
        type="button"
        @click="insertLink"
        :class="[
          'p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700',
          activeFormats.link ? 'bg-gray-300 dark:bg-gray-600' : '',
        ]"
        title="Inserir link"
      >
        <LinkIcon class="h-4 w-4" />
      </button>

      <!-- Limpar formatação -->
      <button
        type="button"
        @click="clearFormatting"
        class="p-2 rounded hover:bg-gray-200 dark:hover:bg-gray-700"
        title="Limpar formatação"
      >
        <TrashIcon class="h-4 w-4" />
      </button>
    </div>

    <!-- Textarea editável -->
    <div
      ref="editor"
      class="editor border border-t-0 border-gray-300 dark:border-gray-600 rounded-b-lg bg-white dark:bg-gray-900 min-h-[200px] p-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
      contenteditable="true"
      @input="handleInput"
      @keydown="handleKeydown"
      @focus="handleFocus"
      @blur="handleBlur"
      @paste="handlePaste"
      @click="saveSelection"
      @keyup="saveSelection"
    ></div>

    <!-- Contador de caracteres -->
    <div
      class="flex justify-between items-center mt-2 text-sm text-gray-500 dark:text-gray-400"
    >
      <div class="flex items-center gap-2">
        <span>{{ characterCount }} caracteres</span>
        <span v-if="wordCount > 0">{{ wordCount }} palavras</span>
      </div>
      <div>
        <span :class="characterCount > maxLength ? 'text-red-500' : ''">
          {{ maxLength - characterCount }} restantes
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from "vue";
import {
  BoldIcon,
  ItalicIcon,
  UnderlineIcon,
  ListBulletIcon,
  Bars4Icon,
  Bars3BottomLeftIcon,
  Bars3Icon,
  Bars3BottomRightIcon,
  LinkIcon,
  TrashIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  modelValue: {
    type: String,
    default: "",
  },
  maxLength: {
    type: Number,
    default: 5000,
  },
  placeholder: {
    type: String,
    default: "Digite seu comentário aqui...",
  },
});

const emit = defineEmits(["update:modelValue", "input"]);

const editor = ref(null);
const activeFormats = ref({
  bold: false,
  italic: false,
  underline: false,
  ul: false,
  ol: false,
  alignLeft: false,
  alignCenter: false,
  alignRight: false,
  link: false,
});

// Para manter a seleção do cursor
let lastSelection = null;

// Computed properties
const htmlContent = computed(() => {
  return editor.value?.innerHTML || "";
});

const plainText = computed(() => {
  return editor.value?.innerText || "";
});

const characterCount = computed(() => {
  return plainText.value.length;
});

const wordCount = computed(() => {
  return plainText.value.trim() ? plainText.value.trim().split(/\s+/).length : 0;
});

// Métodos para gerenciar seleção
const saveSelection = () => {
  const selection = window.getSelection();
  if (selection.rangeCount > 0) {
    const range = selection.getRangeAt(0);
    if (editor.value.contains(range.commonAncestorContainer)) {
      lastSelection = range;
    }
  }
};

const restoreSelection = () => {
  if (!lastSelection || !editor.value) return;

  try {
    const selection = window.getSelection();
    selection.removeAllRanges();
    selection.addRange(lastSelection);
  } catch (error) {
    console.error("Error restoring selection:", error);
    // Fallback: coloca o cursor no final do conteúdo
    placeCursorAtEnd();
  }
};

const placeCursorAtEnd = () => {
  if (!editor.value) return;

  const range = document.createRange();
  const selection = window.getSelection();

  range.selectNodeContents(editor.value);
  range.collapse(false); // false colapsa ao final

  selection.removeAllRanges();
  selection.addRange(range);
};

const placeCursorAtStart = () => {
  if (!editor.value) return;

  const range = document.createRange();
  const selection = window.getSelection();

  range.selectNodeContents(editor.value);
  range.collapse(true); // true colapsa ao início

  selection.removeAllRanges();
  selection.addRange(range);
};

const getCurrentCursorPosition = () => {
  const selection = window.getSelection();
  if (selection.rangeCount === 0) return null;

  const range = selection.getRangeAt(0);
  const preCaretRange = range.cloneRange();
  preCaretRange.selectNodeContents(editor.value);
  preCaretRange.setEnd(range.endContainer, range.endOffset);

  return preCaretRange.toString().length;
};

const setCursorPosition = (position) => {
  if (!editor.value) return;

  const textNode = getTextNodeAtPosition(editor.value, position);
  if (!textNode) {
    placeCursorAtEnd();
    return;
  }

  const range = document.createRange();
  const selection = window.getSelection();

  range.setStart(textNode.node, position - textNode.offset);
  range.collapse(true);

  selection.removeAllRanges();
  selection.addRange(range);
};

const getTextNodeAtPosition = (root, index) => {
  const treeWalker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, null);

  let currentNode = treeWalker.nextNode();
  let currentIndex = 0;

  while (currentNode) {
    const nodeLength = currentNode.length;
    if (index >= currentIndex && index <= currentIndex + nodeLength) {
      return {
        node: currentNode,
        offset: currentIndex,
      };
    }
    currentIndex += nodeLength;
    currentNode = treeWalker.nextNode();
  }

  return null;
};

// Métodos principais
const formatText = (command, value = null) => {
  if (!editor.value) return;

  // Salva a posição atual do cursor
  saveSelection();

  // Foca no editor e restaura a seleção
  editor.value.focus();
  restoreSelection();

  try {
    if (command === "createLink") {
      const url = value || window.prompt("Digite a URL:");
      if (url) {
        document.execCommand(command, false, url);
      }
    } else {
      document.execCommand(command, false, value);
    }

    // Atualiza o estado de formatação
    checkFormatting();

    // Atualiza o valor do modelo
    updateModelValue();

    // Salva novamente a seleção após a formatação
    saveSelection();
  } catch (error) {
    console.error("Error executing command:", error);
  }
};

const insertLink = () => {
  const selection = window.getSelection();
  if (!selection.toString().trim()) {
    alert("Selecione o texto que deseja transformar em link");
    return;
  }
  formatText("createLink");
};

const clearFormatting = () => {
  if (!editor.value) return;

  // Salva a posição atual do cursor
  saveSelection();

  editor.value.focus();
  restoreSelection();

  document.execCommand("removeFormat", false, null);
  document.execCommand("unlink", false, null);

  checkFormatting();
  updateModelValue();

  // Salva novamente a seleção
  saveSelection();
};

const checkFormatting = () => {
  if (!editor.value || !document.queryCommandSupported) return;

  activeFormats.value = {
    bold: document.queryCommandState("bold"),
    italic: document.queryCommandState("italic"),
    underline: document.queryCommandState("underline"),
    ul: document.queryCommandState("insertUnorderedList"),
    ol: document.queryCommandState("insertOrderedList"),
    alignLeft: document.queryCommandState("justifyLeft"),
    alignCenter: document.queryCommandState("justifyCenter"),
    alignRight: document.queryCommandState("justifyRight"),
    link: document.queryCommandState("unlink"),
  };
};

const handleInput = () => {
  updateModelValue();
  saveSelection();
};

const handleKeydown = (event) => {
  // Salva a seleção antes de qualquer modificação por teclado
  saveSelection();

  // Prevenir Enter de criar divs
  if (event.key === "Enter" && !event.shiftKey) {
    event.preventDefault();
    document.execCommand("insertLineBreak");
  }

  // Limitar caracteres
  if (
    characterCount.value >= props.maxLength &&
    event.key.length === 1 &&
    !event.ctrlKey &&
    !event.metaKey &&
    !event.altKey
  ) {
    event.preventDefault();
  }
};

const handleFocus = () => {
  // Quando o editor recebe foco, se estiver vazio, coloca o cursor no início
  if (editor.value && !editor.value.textContent.trim()) {
    placeCursorAtStart();
  }
  checkFormatting();
};

const handleBlur = () => {
  checkFormatting();
};

const handlePaste = (event) => {
  event.preventDefault();

  // Salva a posição atual do cursor
  const cursorPos = getCurrentCursorPosition();

  // Remove formatação do texto colado
  const text = event.clipboardData.getData("text/plain");

  // Insere texto preservando quebras de linha
  const lines = text.split("\n");
  let insertedLength = 0;

  lines.forEach((line, index) => {
    document.execCommand("insertText", false, line);
    insertedLength += line.length;

    if (index < lines.length - 1) {
      document.execCommand("insertLineBreak", false, null);
      insertedLength += 1; // Para a quebra de linha
    }
  });

  updateModelValue();

  // Tenta restaurar a posição do cursor após a colagem
  if (cursorPos !== null) {
    nextTick(() => {
      setCursorPosition(cursorPos + insertedLength);
    });
  }
};

const updateModelValue = () => {
  const html = editor.value?.innerHTML || "";
  emit("update:modelValue", html);
  emit("input", html);
};

const setContent = (html) => {
  if (editor.value) {
    // Salva se estava focado
    const wasFocused = document.activeElement === editor.value;

    editor.value.innerHTML = html;

    // Se estava focado, restaura o foco e coloca o cursor no final
    if (wasFocused) {
      nextTick(() => {
        editor.value.focus();
        placeCursorAtEnd();
      });
    }
  }
};

// Inicialização
onMounted(() => {
  if (props.modelValue) {
    setContent(props.modelValue);
  }

  // Adicionar placeholder
  if (editor.value) {
    editor.value.setAttribute("data-placeholder", props.placeholder);

    if (!props.modelValue) {
      editor.value.innerHTML = "";
    }
  }

  // Adiciona um event listener global para salvar seleção
  document.addEventListener("selectionchange", saveSelection);
});

// Watch para modelValue externo
watch(
  () => props.modelValue,
  (newValue) => {
    if (editor.value && newValue !== htmlContent.value) {
      setContent(newValue);
    }
  }
);

// Limpeza
onUnmounted(() => {
  document.removeEventListener("selectionchange", saveSelection);
});
</script>

<style scoped>
.editor[contenteditable="true"]:empty:before {
  content: attr(data-placeholder);
  color: #9ca3af;
  pointer-events: none;
}

.editor[contenteditable="true"] {
  white-space: pre-wrap;
  word-wrap: break-word;
  overflow-wrap: break-word;
}

.editor[contenteditable="true"]:focus {
  outline: none;
}

.editor[contenteditable="true"] ul,
.editor[contenteditable="true"] ol {
  margin-left: 1.5em;
  padding-left: 0;
}

.editor[contenteditable="true"] a {
  color: #3b82f6;
  text-decoration: underline;
}

.editor[contenteditable="true"] a:hover {
  color: #2563eb;
}

.toolbar button {
  transition: all 0.2s ease;
}

.toolbar button:active {
  transform: scale(0.95);
}

.dark .editor[contenteditable="true"]:empty:before {
  color: #6b7280;
}
</style>
