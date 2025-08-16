<template>
    <Head title="Logs" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Logs de Atividade
            </h2>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Buscar
                                </label>
                                <input 
                                    v-model="filters.search"
                                    @input="handleFilter"
                                    type="text" 
                                    placeholder="Nome do cliente ou chave..."
                                    class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Ação
                                </label>
                                <select 
                                    v-model="filters.action"
                                    @change="handleFilter"
                                    class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                >
                                    <option value="">Todas as Ações</option>
                                    <option value="verify">Verificação</option>
                                    <option value="created">Criação</option>
                                    <option value="renewed">Renovação</option>
                                    <option value="deactivated">Desativação</option>
                                    <option value="expired">Expiração</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Licença
                                </label>
                                <input 
                                    v-model="filters.license_id"
                                    @input="handleFilter"
                                    type="text" 
                                    placeholder="ID da licença..."
                                    class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                            </div>
                            <div class="flex items-end">
                                <button 
                                    @click="clearFilters"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                                >
                                    Limpar Filtros
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Logs Table -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Data/Hora
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Ação
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Cliente
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Chave de Acesso
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        IP
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Dispositivo
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ formatDateTime(log.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div :class="getActionColor(log.action)" class="inline-flex items-center justify-center h-8 w-8 rounded-full mr-3">
                                                <svg :class="getActionIconClass(log.action)" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path v-if="log.action === 'verify'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                                    <path v-else-if="log.action === 'created'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                    <path v-else-if="log.action === 'renewed'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                    <path v-else-if="log.action === 'deactivated'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    <path v-else-if="log.action === 'expired'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                            <span :class="getActionBadgeColor(log.action)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                                {{ getActionLabel(log.action) }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ log.license?.client_name || 'N/A' }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ log.license?.client_email || 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <code class="text-sm bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                                            {{ log.license?.access_key || 'N/A' }}
                                        </code>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ log.ip_address }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                        <div v-if="log.device_id" class="max-w-xs truncate">
                                            {{ log.device_id }}
                                        </div>
                                        <div v-else>-</div>
                                        <div v-if="log.user_agent" class="text-xs text-gray-400 dark:text-gray-500 max-w-xs truncate mt-1" :title="log.user_agent">
                                            {{ log.user_agent.substring(0, 50) }}...
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div v-if="logs.data.length === 0" class="p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 48 48">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M34 40h10v-4a6 6 0 00-10.712-3.714M34 40H14m20 0v-4a9.971 9.971 0 00-.712-3.714M14 40H4v-4a6 6 0 0110.713-3.714M14 40v-4c0-1.313.253-2.566.713-3.714m0 0A10.003 10.003 0 0124 26c4.21 0 7.813 2.602 9.288 6.286"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">Nenhum log encontrado</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Não há logs de atividade com os filtros aplicados.</p>
                    </div>

                    <!-- Pagination -->
                    <div v-if="logs.data.length > 0" class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 flex justify-between sm:hidden">
                                <Link 
                                    v-if="logs.prev_page_url" 
                                    :href="logs.prev_page_url" 
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    Anterior
                                </Link>
                                <Link 
                                    v-if="logs.next_page_url" 
                                    :href="logs.next_page_url" 
                                    class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                                >
                                    Próximo
                                </Link>
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700 dark:text-gray-300">
                                        Mostrando
                                        <span class="font-medium">{{ logs.from }}</span>
                                        até
                                        <span class="font-medium">{{ logs.to }}</span>
                                        de
                                        <span class="font-medium">{{ logs.total }}</span>
                                        resultados
                                    </p>
                                </div>
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                        <Link 
                                            v-if="logs.prev_page_url" 
                                            :href="logs.prev_page_url" 
                                            class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600"
                                        >
                                            Anterior
                                        </Link>
                                        <Link 
                                            v-if="logs.next_page_url" 
                                            :href="logs.next_page_url" 
                                            class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-sm font-medium text-gray-500 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600"
                                        >
                                            Próximo
                                        </Link>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    logs: Object,
    filters: Object
});

const filters = reactive({
    search: props.filters?.search || '',
    action: props.filters?.action || '',
    license_id: props.filters?.license_id || ''
});

const handleFilter = () => {
    router.get(route('logs'), filters, { 
        preserveState: true,
        replace: true
    });
};

const clearFilters = () => {
    filters.search = '';
    filters.action = '';
    filters.license_id = '';
    handleFilter();
};

const formatDateTime = (date) => {
    return new Date(date).toLocaleString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
};

const getActionColor = (action) => {
    const colors = {
        verify: 'bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400',
        created: 'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400',
        renewed: 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900 dark:text-indigo-400',
        deactivated: 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-400',
        expired: 'bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-400'
    };
    return colors[action] || 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400';
};

const getActionBadgeColor = (action) => {
    const colors = {
        verify: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        created: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        renewed: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
        deactivated: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        expired: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
    };
    return colors[action] || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';
};

const getActionIconClass = (action) => {
    const classes = {
        verify: 'text-blue-600 dark:text-blue-400',
        created: 'text-green-600 dark:text-green-400',
        renewed: 'text-indigo-600 dark:text-indigo-400',
        deactivated: 'text-yellow-600 dark:text-yellow-400',
        expired: 'text-red-600 dark:text-red-400'
    };
    return classes[action] || 'text-gray-600 dark:text-gray-400';
};

const getActionLabel = (action) => {
    const labels = {
        verify: 'Verificação',
        created: 'Criação',
        renewed: 'Renovação',
        deactivated: 'Desativação',
        expired: 'Expiração'
    };
    return labels[action] || action;
};
</script>
