<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import {
  FolderIcon,
  PhotoIcon,
  DocumentIcon,
  DocumentDuplicateIcon,
  ArrowDownTrayIcon,
  TrashIcon,
  ArrowLeftIcon,
  FolderPlusIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({ contents: Array, currentPath: String });

// Referencias y formularios existentes
const fileInput = ref(null);
const form = useForm({ file: null });
const newFolderName = ref('');

// Drag and drop
const isDragging = ref(false);
const dropZone = ref(null);

const showCreateFolderModal = ref(false);

// Funciones existentes
const uploadFile = () => {
  form.post(route('ftp.upload', { path: encodeURIComponent(props.currentPath) }), {
    forceFormData: true,
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    onSuccess: () => form.reset(),
  });
};

const deleteItem = (path) => {
  router.delete(route('ftp.delete', { path: encodeURIComponent(path) }), {
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
  });
};

const isImage = (fileName) => {
  if (!fileName) return false;
  const ext = fileName.split('.').pop().toLowerCase();
  return ['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(ext);
};

const openFolder = (path) => {
  router.get(route('ftp.index', { path: encodeURIComponent(path) }));
};

const goBack = () => {
  const parentPath = props.currentPath.split('/').slice(0, -1).join('/');
  router.get(route('ftp.index', { path: encodeURIComponent(parentPath || '/') }));
};

const createFolder = () => {
  if (newFolderName.value) {
    router.post(route('ftp.createFolder', { path: encodeURIComponent(props.currentPath) }), { name: newFolderName.value }, {
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      onSuccess: () => {
        newFolderName.value = '';
        closeCreateFolderModal();
      }
    });
  }
};

const openCreateFolderModal = () => {
  showCreateFolderModal.value = true;
};

const closeCreateFolderModal = () => {
  showCreateFolderModal.value = false;
};

const getFileName = (path) => {
  return path.split('/').pop();
};

const getBreadcrumbs = () => {
  const parts = props.currentPath.split('/').filter(Boolean);
  return parts.map((part, index) => ({
    name: part,
    path: '/' + parts.slice(0, index + 1).join('/')
  }));
};

// Drag and drop handlers
const handleDragOver = (e) => {
  if (e.dataTransfer.types.includes('Files')) {
    e.preventDefault();
    isDragging.value = true;
  }
};

const handleDragLeave = (e) => {
  if (!dropZone.value.contains(e.relatedTarget)) {
    isDragging.value = false;
  }
};

const handleDrop = async (e) => {
  e.preventDefault();
  isDragging.value = false;

  const files = e.dataTransfer.files;
  if (files.length > 0) {
    const formData = new FormData();

    Array.from(files).forEach(file => {
      formData.append('files[]', file);
    });

    try {
      await router.post(
        route('ftp.upload', { path: encodeURIComponent(props.currentPath) }),
        formData,
        {
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'multipart/form-data'
          },
          forceFormData: true
        }
      );
      form.reset();
    } catch (error) {
      console.error('Error uploading files:', error);
    }
  }
};

// Event listeners
onMounted(() => {
  window.addEventListener('dragover', handleDragOver);
  window.addEventListener('dragleave', handleDragLeave);
  window.addEventListener('drop', handleDrop);
});

onUnmounted(() => {
  window.removeEventListener('dragover', handleDragOver);
  window.removeEventListener('dragleave', handleDragLeave);
  window.removeEventListener('drop', handleDrop);
});
</script>

<template>
  <div class="container p-4 mx-auto">
    <div class="p-4 bg-white rounded-lg shadow dark:bg-gray-800">
      <!-- Toolbar -->
      <div class="flex gap-2 p-2 mb-4 bg-gray-100 rounded-lg dark:bg-gray-700">
        <button v-if="currentPath !== '/'" @click="goBack"
          class="p-2 text-white bg-gray-500 rounded hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-700">
          <ArrowLeftIcon class="inline w-5 h-5" /> Volver
        </button>
        <button @click="fileInput?.click()"
          class="p-2 text-white bg-blue-500 rounded hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700">
          Subir Archivo
        </button>
        <button @click="uploadFile"
          class="p-2 text-white bg-blue-600 rounded hover:bg-blue-700 dark:bg-blue-700 dark:hover:bg-blue-800">
          Confirmar Subida
        </button>
        <div class="flex-grow"></div>
        <button @click="openCreateFolderModal"
          class="p-2 text-white bg-green-500 rounded hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700">
          <FolderPlusIcon class="w-5 h-5" />
        </button>
        <input type="file" ref="fileInput" @change="form.file = $event.target.files[0]" hidden />
      </div>

      <!-- Modal Crear Carpeta -->
      <div v-if="showCreateFolderModal"
        class="fixed inset-0 z-20 flex items-center justify-center bg-black bg-opacity-50">
        <div class="p-4 bg-white rounded shadow dark:bg-gray-800">
          <h2 class="mb-2 text-lg font-bold dark:text-gray-200">Crear carpeta</h2>
          <input type="text" v-model="newFolderName"
            class="p-2 mb-4 border rounded dark:bg-gray-600 dark:border-gray-500" />
          <div class="flex justify-end gap-2">
            <button @click="createFolder" class="px-4 py-2 text-white bg-blue-600 rounded">
              Aceptar
            </button>
            <button @click="closeCreateFolderModal" class="px-4 py-2 text-white bg-gray-500 rounded">
              Cancelar
            </button>
          </div>
        </div>
      </div>

      <!-- Breadcrumb -->
      <div class="flex gap-2 p-2 mb-4 text-sm text-gray-600 rounded-lg bg-gray-50 dark:bg-gray-700 dark:text-gray-300">
        <span @click="openFolder('/')" class="flex items-center cursor-pointer hover:underline">Root</span>
        <span v-for="(breadcrumb, index) in getBreadcrumbs()" :key="breadcrumb.path" class="flex items-center">
          <span class="mx-1">/</span>
          <span @click="openFolder(breadcrumb.path)" class="cursor-pointer hover:underline"
            :class="{ 'font-bold text-blue-600 dark:text-blue-400': index === getBreadcrumbs().length - 1 }">
            {{ breadcrumb.name }}
          </span>
        </span>
      </div>

      <!-- File Grid con Drag and Drop -->
      <div ref="dropZone"
        class="relative grid grid-cols-2 gap-4 min-h-[300px] sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6"
        @dragover.prevent="handleDragOver" @dragleave.prevent="handleDragLeave" @drop.prevent="handleDrop">
        <!-- Overlay de Drag and Drop -->
        <div v-if="isDragging"
          class="absolute inset-0 z-10 flex items-center justify-center bg-white border-4 border-blue-500 border-dashed rounded-lg bg-opacity-90 animate-pulse dark:bg-gray-800 dark:bg-opacity-90">
          <div class="text-center text-blue-600 dark:text-blue-400">
            <ArrowDownTrayIcon class="w-16 h-16 mx-auto mb-4" />
            <p class="text-xl font-bold">Suelta los archivos para subirlos</p>
          </div>
        </div>

        <!-- Archivos y carpetas -->
        <div v-for="item in contents" :key="item.path"
          class="p-2 border rounded-lg cursor-pointer hover:bg-gray-50 group h-36 dark:border-gray-600 dark:hover:bg-gray-700">
          <div @click="item.type === 'dir' && openFolder(item.path)" class="text-center">
            <template v-if="item.type === 'dir'">
              <FolderIcon class="w-12 h-12 mx-auto text-yellow-500" />
              <span class="block truncate dark:text-gray-300">{{ getFileName(item.path) }}</span>
            </template>
            <template v-else-if="isImage(item.path)">
              <PhotoIcon class="w-12 h-12 mx-auto text-blue-500" />
              <span class="block truncate dark:text-gray-300">{{ getFileName(item.path) }}</span>
            </template>
            <template v-else>
              <DocumentIcon class="w-12 h-12 mx-auto text-gray-500 dark:text-gray-400" />
              <span class="block truncate dark:text-gray-300">{{ getFileName(item.path) }}</span>
            </template>
          </div>
          <div class="flex justify-center gap-2 mt-2 transition-opacity opacity-0 group-hover:opacity-100">
            <button @click.stop="copyLink(item.path)"
              class="text-gray-500 hover:text-blue-500 dark:text-gray-400 dark:hover:text-blue-400">
              <DocumentDuplicateIcon class="w-5 h-5" />
            </button>
            <button @click.stop="downloadFile(item.path)"
              class="text-gray-500 hover:text-green-500 dark:text-gray-400 dark:hover:text-green-400">
              <ArrowDownTrayIcon class="w-5 h-5" />
            </button>
            <button @click.stop="deleteItem(item.path)"
              class="text-gray-500 hover:text-red-500 dark:text-gray-400 dark:hover:text-red-400">
              <TrashIcon class="w-5 h-5" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>