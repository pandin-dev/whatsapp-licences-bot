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
                                Nova Licença
                            </DialogTitle>
                            
                            <form @submit.prevent="createLicense" class="mt-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Nome do Cliente <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        v-model="form.client_name"
                                        type="text" 
                                        required
                                        class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        placeholder="Nome completo do cliente"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        E-mail do Cliente
                                    </label>
                                    <input 
                                        v-model="form.client_email"
                                        type="email" 
                                        class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        placeholder="email@exemplo.com (opcional)"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Número do Cliente
                                    </label>
                                    <input 
                                        v-model="form.client_phone"
                                        type="tel" 
                                        class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        placeholder="(11) 99999-9999 (opcional)"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Valor da Licença <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500 dark:text-gray-400">R$</span>
                                        <input 
                                            v-model="form.license_value"
                                            type="number" 
                                            step="0.01"
                                            min="0"
                                            required
                                            class="block w-full pl-10 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                            placeholder="0.00"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Tipo de Plano <span class="text-red-500">*</span>
                                    </label>
                                    <select 
                                        v-model="form.plan_type"
                                        required
                                        class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                    >
                                        <option value="">Selecione um plano</option>
                                        <option value="basic">Básico</option>
                                        <option value="professional">Profissional</option>
                                        <option value="enterprise">Empresarial</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Duração (dias) <span class="text-red-500">*</span>
                                    </label>
                                    <input 
                                        v-model.number="form.duration_days"
                                        type="number" 
                                        min="0" 
                                        max="365"
                                        required
                                        class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        placeholder="30"
                                    />
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        0 = Lifetime | 1-365 dias = Prazo determinado
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
                                        {{ loading ? 'Criando...' : 'Criar Licença' }}
                                    </button>
                                </div>
                            </form>
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
    }
});

const emit = defineEmits(['close', 'created']);

const loading = ref(false);
const error = ref('');

const form = reactive({
    client_name: '',
    client_email: '',
    client_phone: '',
    license_value: '',
    plan_type: '',
    duration_days: 30
});

const resetForm = () => {
    form.client_name = '';
    form.client_email = '';
    form.client_phone = '';
    form.license_value = '';
    form.plan_type = '';
    form.duration_days = 30;
    error.value = '';
};

const createLicense = async () => {
    loading.value = true;
    error.value = '';

    try {
        // Usar axios com configuração CSRF automática para rotas web
        const response = await axios.post('/admin/licenses/generate', form);
        
        if (response.data.success) {
            emit('created', response.data);
            resetForm();
        } else {
            error.value = response.data.message || 'Erro ao criar licença';
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Erro ao criar licença';
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
