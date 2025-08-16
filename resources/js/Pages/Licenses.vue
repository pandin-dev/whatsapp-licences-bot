<template>
    <Head title="Licenças" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Gerenciar Licenças
                </h2>
                <button 
                    @click="showCreateModal = true"
                    class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-900 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nova Licença
                </button>
            </div>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Dashboard Charts -->
                <LicenseDashboard :licenses="licenses" />
                
                <!-- Flash Messages -->
                <div v-if="$page.props.flash?.success" class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative dark:bg-green-800 dark:border-green-600 dark:text-green-100" role="alert">
                    <strong class="font-bold">Sucesso!</strong>
                    <span class="block sm:inline">{{ $page.props.flash.success }}</span>
                </div>
                
                <div v-if="$page.props.flash?.error" class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative dark:bg-red-800 dark:border-red-600 dark:text-red-100" role="alert">
                    <strong class="font-bold">Erro!</strong>
                    <span class="block sm:inline">{{ $page.props.flash.error }}</span>
                </div>
                
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
                                    placeholder="Nome, email ou chave..."
                                    class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Status
                                </label>
                                <select 
                                    v-model="filters.status"
                                    @change="handleFilter"
                                    class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                >
                                    <option value="">Todos os Status</option>
                                    <option value="active">Ativo</option>
                                    <option value="inactive">Inativo</option>
                                    <option value="expired">Expirado</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Plano
                                </label>
                                <select 
                                    v-model="filters.plan_type"
                                    @change="handleFilter"
                                    class="block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 rounded-md shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                >
                                    <option value="">Todos os Planos</option>
                                    <option value="basic">Básico</option>
                                    <option value="professional">Profissional</option>
                                    <option value="enterprise">Empresarial</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <button 
                                    @click="clearFilters"
                                    class="w-full px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-md transition-colors duration-200"
                                >
                                    Limpar Filtros
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Licenses Table -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Cliente
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Chave de Acesso
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Valor
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Plano
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Expira em
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Último Acesso
                                    </th>
                                    <th class="relative px-6 py-3">
                                        <span class="sr-only">Ações</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr v-for="license in licenses.data" :key="license.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                {{ license.client_name }}
                                            </div>
                                            <div v-if="license.client_email" class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ license.client_email }}
                                            </div>
                                            <div v-if="license.client_phone" class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ license.client_phone }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <code class="text-sm bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                                                {{ license.access_key }}
                                            </code>
                                            <button 
                                                @click="copyToClipboard(license.access_key)"
                                                class="ml-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        <span class="font-medium">
                                            R$ {{ formatCurrency(license.license_value) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getPlanTypeColor(license.plan_type)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                            {{ getPlanTypeLabel(license.plan_type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getStatusColor(license.status)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                            {{ getStatusLabel(license.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        <div v-if="license.expires_at">
                                            {{ formatDate(license.expires_at) }}
                                        </div>
                                        <div v-else class="flex items-center">
                                            <svg class="w-4 h-4 text-green-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            <span class="text-green-600 dark:text-green-400 font-medium">Lifetime</span>
                                        </div>
                                        <div v-if="license.expires_at" class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ getDaysUntilExpiry(license.expires_at) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        <div v-if="license.last_verification">
                                            {{ formatDate(license.last_verification) }}
                                        </div>
                                        <div v-else class="text-gray-400 dark:text-gray-500">
                                            Nunca
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <button 
                                                @click="showRenewModal(license)"
                                                class="text-primary-600 hover:text-primary-900 dark:text-primary-400 dark:hover:text-primary-300"
                                                title="Renovar"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                </svg>
                                            </button>
                                            
                                            <!-- Botão Ativar - só aparece para licenças inativas -->
                                            <button 
                                                v-if="license.status === 'inactive'"
                                                @click="activateLicense(license)"
                                                class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                                                title="Ativar"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </button>
                                            
                                            <!-- Botão Desativar - só aparece para licenças ativas -->
                                            <button 
                                                v-if="license.status === 'active'"
                                                @click="deactivateLicense(license)"
                                                class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300"
                                                title="Desativar"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L18.364 5.636M5.636 18.364l12.728-12.728"/>
                                                </svg>
                                            </button>
                                            
                                            <button 
                                                @click="deleteLicense(license)"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                title="Deletar permanentemente"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="licenses.links" class="bg-white dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 flex justify-between sm:hidden">
                                <a v-if="licenses.links.prev" :href="licenses.links.prev" class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    Anterior
                                </a>
                                <a v-if="licenses.links.next" :href="licenses.links.next" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    Próxima
                                </a>
                            </div>
                            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm text-gray-700 dark:text-gray-300">
                                        Mostrando 
                                        <span class="font-medium">{{ licenses.from }}</span>
                                        até 
                                        <span class="font-medium">{{ licenses.to }}</span>
                                        de 
                                        <span class="font-medium">{{ licenses.total }}</span>
                                        resultados
                                    </p>
                                </div>
                                <div>
                                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                        <template v-for="link in licenses.links" :key="link.label">
                                            <a 
                                                v-if="link.url" 
                                                :href="link.url"
                                                :class="[
                                                    link.active 
                                                        ? 'bg-primary-50 border-primary-500 text-primary-600 dark:bg-primary-900 dark:border-primary-600 dark:text-primary-300' 
                                                        : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400 dark:hover:bg-gray-700',
                                                    'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
                                                ]"
                                                v-html="link.label"
                                            ></a>
                                            <span 
                                                v-else 
                                                :class="[
                                                    'relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400'
                                                ]"
                                                v-html="link.label"
                                            ></span>
                                        </template>
                                    </nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create License Modal -->
        <CreateLicenseModal
            :show="showCreateModal"
            @close="showCreateModal = false"
            @created="handleLicenseCreated"
        />

        <!-- Renew License Modal -->
        <RenewLicenseModal
            :show="showRenewModalVisible"
            :license="selectedLicense"
            @close="showRenewModalVisible = false"
            @renewed="handleLicenseRenewed"
        />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CreateLicenseModal from '@/Components/CreateLicenseModal.vue';
import RenewLicenseModal from '@/Components/RenewLicenseModal.vue';
import LicenseDashboard from '@/Components/Dashboard/LicenseDashboard.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    licenses: Object,
    filters: Object
});

const showCreateModal = ref(false);
const showRenewModalVisible = ref(false);
const selectedLicense = ref(null);

const filters = reactive({
    search: props.filters?.search || '',
    status: props.filters?.status || '',
    plan_type: props.filters?.plan_type || ''
});

const handleFilter = () => {
    router.get('/licenses', filters, {
        preserveState: true,
        replace: true
    });
};

const clearFilters = () => {
    filters.search = '';
    filters.status = '';
    filters.plan_type = '';
    handleFilter();
};

const showRenewModal = (license) => {
    selectedLicense.value = license;
    showRenewModalVisible.value = true;
};

const handleLicenseCreated = () => {
    showCreateModal.value = false;
    router.reload();
};

const handleLicenseRenewed = () => {
    showRenewModalVisible.value = false;
    router.reload();
};

const deactivateLicense = (license) => {
    if (confirm('Tem certeza que deseja desativar esta licença?')) {
        // Salva a posição atual de scroll
        const scrollY = window.scrollY;
        
        router.post(`/admin/licenses/${license.id}/deactivate`, {
            _method: 'DELETE'
        }, {
            onFinish: () => {
                // Restaura a posição de scroll após a operação
                setTimeout(() => {
                    window.scrollTo(0, scrollY);
                }, 100);
            }
        });
    }
};

const activateLicense = (license) => {
    if (confirm('Tem certeza que deseja ativar esta licença?')) {
        // Salva a posição atual de scroll
        const scrollY = window.scrollY;
        
        router.post(`/admin/licenses/${license.id}/activate`, {}, {
            onFinish: () => {
                // Restaura a posição de scroll após a operação
                setTimeout(() => {
                    window.scrollTo(0, scrollY);
                }, 100);
            }
        });
    }
};

const deleteLicense = (license) => {
    if (confirm('⚠️ ATENÇÃO: Esta ação é irreversível!\n\nTem certeza que deseja DELETAR PERMANENTEMENTE esta licença?\n\nTodos os dados relacionados serão perdidos para sempre.')) {
        // Salva a posição atual de scroll
        const scrollY = window.scrollY;
        
        router.post(`/admin/licenses/${license.id}/delete`, {
            _method: 'DELETE'
        }, {
            onFinish: () => {
                // Restaura a posição de scroll após a operação
                setTimeout(() => {
                    window.scrollTo(0, scrollY);
                }, 100);
            }
        });
    }
};

const copyToClipboard = (text) => {
    navigator.clipboard.writeText(text);
};

const formatDate = (date) => {
    if (!date) return '';
    return new Date(date).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};

const formatCurrency = (value) => {
    return new Intl.NumberFormat('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
};

const getDaysUntilExpiry = (date) => {
    if (!date) return '';
    const days = Math.ceil((new Date(date) - new Date()) / (1000 * 60 * 60 * 24));
    if (days < 0) return 'Expirado';
    if (days === 0) return 'Expira hoje';
    if (days === 1) return 'Expira amanhã';
    return `${days} dias restantes`;
};

const getStatusColor = (status) => {
    const colors = {
        active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        inactive: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        expired: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
    };
    return colors[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';
};

const getStatusLabel = (status) => {
    const labels = {
        active: 'Ativo',
        inactive: 'Inativo',
        expired: 'Expirado'
    };
    return labels[status] || status;
};

const getPlanTypeColor = (planType) => {
    const colors = {
        basic: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
        professional: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        enterprise: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300'
    };
    return colors[planType] || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';
};

const getPlanTypeLabel = (planType) => {
    const labels = {
        basic: 'Básico',
        professional: 'Profissional',
        enterprise: 'Empresarial'
    };
    return labels[planType] || planType;
};
</script>
