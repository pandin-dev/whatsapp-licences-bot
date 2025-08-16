<template>
    <TransitionRoot appear :show="show" as="template">
        <Dialog as="div" @close="$emit('close')" class="relative z-50">
            <TransitionChild
                as="template"
                enter="duration-300 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-200 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black bg-opacity-25" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <TransitionChild
                        as="template"
                        enter="duration-300 ease-out"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="duration-200 ease-in"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 p-6 text-left align-middle shadow-xl transition-all">
                            <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100">
                                Renovar Licença
                            </DialogTitle>
                            
                            <div v-if="license" class="mt-4">
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-4">
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">{{ license.client_name }}</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ license.client_email }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        Chave: <code class="bg-gray-200 dark:bg-gray-600 px-1 rounded">{{ license.access_key }}</code>
                                    </p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        Expira atualmente em: {{ formatDate(license.expires_at) }}
                                    </p>
                                </div>

                                <form @submit.prevent="renewLicense" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Extensão (dias)
                                        </label>
                                        <input 
                                            v-model.number="form.duration_days"
                                            type="number" 
                                            min="1" 
                                            max="365"
                                            required
                                            class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                            placeholder="30"
                                        />
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            A licença será estendida por este período a partir de hoje
                                        </p>
                                    </div>

                                    <div v-if="error" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md p-3">
                                        <p class="text-sm text-red-600 dark:text-red-400">{{ error }}</p>
                                    </div>

                                    <div class="flex justify-end space-x-3 mt-6">
                                        <button
                                            type="button"
                                            @click="$emit('close')"
                                            class="inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                                        >
                                            Cancelar
                                        </button>
                                        <button
                                            type="submit"
                                            :disabled="loading"
                                            class="inline-flex justify-center rounded-md border border-transparent bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50"
                                        >
                                            <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            {{ loading ? 'Renovando...' : 'Renovar Licença' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import axios from 'axios';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    license: {
        type: Object,
        default: null
    }
});

const emit = defineEmits(['close', 'renewed']);

const loading = ref(false);
const error = ref('');

const form = reactive({
    duration_days: 30
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('pt-BR');
};

const resetForm = () => {
    form.duration_days = 30;
    error.value = '';
};

const renewLicense = async () => {
    if (!props.license) return;

    loading.value = true;
    error.value = '';

    try {
        const response = await axios.post(`/admin/licenses/${props.license.id}/renew`, form);
        
        if (response.data.success) {
            emit('renewed', response.data);
            resetForm();
        } else {
            error.value = response.data.message || 'Erro ao renovar licença';
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Erro ao renovar licença';
    } finally {
        loading.value = false;
    }
};

watch(() => props.show, (newValue) => {
    if (!newValue) {
        resetForm();
    }
});
</script>
