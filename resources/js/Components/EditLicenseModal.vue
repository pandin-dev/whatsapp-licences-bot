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
                                Editar Licença
                            </DialogTitle>
                            
                            <div v-if="license" class="mt-4">
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-4">
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Chave: <code class="bg-gray-200 dark:bg-gray-600 px-1 rounded">{{ license.access_key }}</code>
                                    </p>
                                </div>

                                <form @submit.prevent="updateLicense" class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Nome do Cliente
                                        </label>
                                        <input 
                                            v-model="form.client_name"
                                            type="text" 
                                            required
                                            class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                            placeholder="Nome do cliente"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Email do Cliente
                                        </label>
                                        <input 
                                            v-model="form.client_email"
                                            type="email" 
                                            required
                                            class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                            placeholder="email@exemplo.com"
                                        />
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Tipo de Plano
                                        </label>
                                        <select 
                                            v-model="form.plan_type"
                                            class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        >
                                            <option value="monthly">Mensal</option>
                                            <option value="lifetime">Lifetime</option>
                                        </select>
                                    </div>

                                    <div v-if="form.plan_type === 'monthly'">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                            Data de Expiração
                                        </label>
                                        <input 
                                            v-model="form.expires_at"
                                            type="date" 
                                            class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        />
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
                                            {{ loading ? 'Atualizando...' : 'Salvar Alterações' }}
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
import { router } from '@inertiajs/vue3';

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

const emit = defineEmits(['close', 'updated']);

const loading = ref(false);
const error = ref('');

const form = reactive({
    client_name: '',
    client_email: '',
    plan_type: 'monthly',
    expires_at: ''
});

const formatDateForInput = (date) => {
    if (!date) return '';
    const d = new Date(date);
    return d.toISOString().split('T')[0];
};

const resetForm = () => {
    if (props.license) {
        form.client_name = props.license.client_name || '';
        form.client_email = props.license.client_email || '';
        form.plan_type = props.license.plan_type || 'monthly';
        form.expires_at = formatDateForInput(props.license.expires_at);
    }
    error.value = '';
};

const saveScrollPosition = () => {
    return window.pageYOffset;
};

const restoreScrollPosition = (position) => {
    setTimeout(() => {
        window.scrollTo(0, position);
    }, 100);
};

const updateLicense = async () => {
    if (!props.license) return;

    loading.value = true;
    error.value = '';

    const scrollPosition = saveScrollPosition();

    try {
        router.put(`/admin/licenses/${props.license.id}/update`, form, {
            preserveScroll: true,
            onSuccess: () => {
                emit('updated');
                emit('close');
                restoreScrollPosition(scrollPosition);
            },
            onError: (errors) => {
                error.value = errors.message || 'Erro ao atualizar licença';
                restoreScrollPosition(scrollPosition);
            },
            onFinish: () => {
                loading.value = false;
            }
        });
    } catch (err) {
        error.value = 'Erro ao atualizar licença';
        loading.value = false;
        restoreScrollPosition(scrollPosition);
    }
};

watch(() => props.show, (newValue) => {
    if (newValue && props.license) {
        resetForm();
    }
});

watch(() => props.license, (newLicense) => {
    if (newLicense && props.show) {
        resetForm();
    }
});
</script>
