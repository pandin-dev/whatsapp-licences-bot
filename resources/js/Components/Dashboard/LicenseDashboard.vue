<template>
    <div>
        <!-- Statistics Cards -->
        <LicenseStats :licenses="licenses" />
        
        <!-- Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- License Status Chart -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Status das Licenças
                    </h3>
                    <div class="h-64 flex items-center justify-center">
                        <canvas ref="statusChart" class="max-w-full max-h-full"></canvas>
                    </div>
                </div>
            </div>

            <!-- Plan Distribution Chart -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Distribuição por Plano
                    </h3>
                    <div class="h-64">
                        <canvas ref="planChart" class="max-w-full max-h-full"></canvas>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Chart -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg lg:col-span-2">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                        Licenças Criadas nos Últimos 7 Dias
                    </h3>
                    <div class="h-64">
                        <canvas ref="activityChart" class="max-w-full max-h-full"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick, onUnmounted, watch } from 'vue';
import { Chart, registerables } from 'chart.js';
import LicenseStats from './LicenseStats.vue';

// Registrar todos os componentes do Chart.js
Chart.register(...registerables);

const props = defineProps({
    licenses: {
        type: Object,
        required: true
    }
});

const statusChart = ref(null);
const planChart = ref(null);
const activityChart = ref(null);

// Instâncias dos gráficos para limpeza posterior
let statusChartInstance = null;
let planChartInstance = null;
let activityChartInstance = null;

// Computed properties para processar dados
const statusData = computed(() => {
    if (!props.licenses?.data) return { active: 0, inactive: 0, expired: 0 };
    
    const counts = { active: 0, inactive: 0, expired: 0 };
    props.licenses.data.forEach(license => {
        counts[license.status] = (counts[license.status] || 0) + 1;
    });
    
    return counts;
});

const planData = computed(() => {
    if (!props.licenses?.data) return { basic: 0, professional: 0, enterprise: 0 };
    
    const counts = { basic: 0, professional: 0, enterprise: 0 };
    props.licenses.data.forEach(license => {
        counts[license.plan_type] = (counts[license.plan_type] || 0) + 1;
    });
    
    return counts;
});

const activityData = computed(() => {
    if (!props.licenses?.data) return [];
    
    const days = [];
    const today = new Date();
    
    // Últimos 7 dias
    for (let i = 6; i >= 0; i--) {
        const date = new Date(today);
        date.setDate(date.getDate() - i);
        const dateStr = date.toISOString().split('T')[0];
        
        const count = props.licenses.data.filter(license => {
            const licenseDate = new Date(license.created_at).toISOString().split('T')[0];
            return licenseDate === dateStr;
        }).length;
        
        days.push({
            date: date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' }),
            count: count
        });
    }
    
    return days;
});

const createStatusChart = () => {
    if (!statusChart.value) return;
    
    // Limpar instância anterior se existir
    if (statusChartInstance) {
        statusChartInstance.destroy();
        statusChartInstance = null;
    }
    
    const data = statusData.value;
    const total = data.active + data.inactive + data.expired;
    
    if (total === 0) {
        // Mostrar mensagem de "sem dados"
        const ctx = statusChart.value.getContext('2d');
        ctx.clearRect(0, 0, statusChart.value.width, statusChart.value.height);
        ctx.fillStyle = '#9CA3AF';
        ctx.font = '14px Inter, system-ui, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText('Nenhum dado disponível', statusChart.value.width / 2, statusChart.value.height / 2);
        return;
    }
    
    const ctx = statusChart.value.getContext('2d');
    
    statusChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Ativo', 'Inativo', 'Expirado'],
            datasets: [{
                data: [data.active, data.inactive, data.expired],
                backgroundColor: [
                    '#10B981', // green-500 - Ativo
                    '#F59E0B', // amber-500 - Inativo 
                    '#EF4444'  // red-500 - Expirado
                ],
                borderColor: [
                    '#10B981',
                    '#F59E0B', 
                    '#EF4444'
                ],
                borderWidth: 3,
                cutout: '60%',
                hoverBackgroundColor: [
                    '#059669', // green-600
                    '#D97706', // amber-600
                    '#DC2626'  // red-600
                ],
                hoverBorderWidth: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: 10
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        font: {
                            size: 13,
                            family: 'Inter, system-ui, sans-serif',
                            weight: '600'
                        },
                        boxWidth: 14,
                        boxHeight: 14,
                        color: '#F9FAFB', // gray-50 - branco para tema dark
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                return data.labels.map((label, i) => {
                                    const dataset = data.datasets[0];
                                    const value = dataset.data[i];
                                    const total = dataset.data.reduce((sum, val) => sum + val, 0);
                                    const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                    
                                    return {
                                        text: `${label} (${value} - ${percentage}%)`,
                                        fillStyle: dataset.backgroundColor[i],
                                        strokeStyle: dataset.backgroundColor[i],
                                        lineWidth: 0,
                                        pointStyle: 'circle',
                                        hidden: false,
                                        index: i,
                                        fontColor: '#F9FAFB' // Força cor branca nos labels
                                    };
                                });
                            }
                            return [];
                        }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleFont: {
                        size: 12,
                        family: 'Inter, system-ui, sans-serif'
                    },
                    bodyFont: {
                        size: 11,
                        family: 'Inter, system-ui, sans-serif'
                    },
                    callbacks: {
                        label: function(context) {
                            const label = context.label;
                            const value = context.parsed;
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            elements: {
                arc: {
                    borderWidth: 0
                }
            }
        }
    });
};

const createPlanChart = () => {
    if (!planChart.value) return;
    
    // Limpar instância anterior se existir
    if (planChartInstance) {
        planChartInstance.destroy();
        planChartInstance = null;
    }
    
    const data = planData.value;
    const ctx = planChart.value.getContext('2d');
    
    planChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Básico', 'Profissional', 'Empresarial'],
            datasets: [{
                label: 'Número de Licenças',
                data: [data.basic, data.professional, data.enterprise],
                backgroundColor: [
                    '#6B7280', // gray-500
                    '#3B82F6', // blue-500
                    '#8B5CF6'  // violet-500
                ],
                borderWidth: 0,
                borderRadius: 6,
                maxBarThickness: 60
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: 20,
                    bottom: 20,
                    left: 20,
                    right: 20
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleFont: {
                        size: 12,
                        family: 'Inter, system-ui, sans-serif'
                    },
                    bodyFont: {
                        size: 11,
                        family: 'Inter, system-ui, sans-serif'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: {
                            size: 11,
                            family: 'Inter, system-ui, sans-serif'
                        },
                        color: '#E5E7EB' // gray-200 - claro para tema dark
                    },
                    grid: {
                        color: 'rgba(229, 231, 235, 0.2)', // gray-200 with opacity
                        drawBorder: false
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 11,
                            family: 'Inter, system-ui, sans-serif'
                        },
                        color: '#E5E7EB' // gray-200 - claro para tema dark
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
};

const createActivityChart = () => {
    if (!activityChart.value) return;
    
    // Limpar instância anterior se existir
    if (activityChartInstance) {
        activityChartInstance.destroy();
        activityChartInstance = null;
    }
    
    const data = activityData.value;
    const ctx = activityChart.value.getContext('2d');
    
    activityChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => d.date),
            datasets: [{
                label: 'Licenças Criadas',
                data: data.map(d => d.count),
                borderColor: '#3B82F6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#3B82F6',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: 20,
                    bottom: 20,
                    left: 20,
                    right: 20
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleFont: {
                        size: 12,
                        family: 'Inter, system-ui, sans-serif'
                    },
                    bodyFont: {
                        size: 11,
                        family: 'Inter, system-ui, sans-serif'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: {
                            size: 11,
                            family: 'Inter, system-ui, sans-serif'
                        },
                        color: '#E5E7EB' // gray-200 - claro para tema dark
                    },
                    grid: {
                        color: 'rgba(229, 231, 235, 0.2)', // gray-200 with opacity
                        drawBorder: false
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 11,
                            family: 'Inter, system-ui, sans-serif'
                        },
                        color: '#E5E7EB' // gray-200 - claro para tema dark
                    },
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
};

const initializeCharts = async () => {
    await nextTick();
    
    // Limpar gráficos existentes
    if (statusChartInstance) {
        statusChartInstance.destroy();
        statusChartInstance = null;
    }
    if (planChartInstance) {
        planChartInstance.destroy();
        planChartInstance = null;
    }
    if (activityChartInstance) {
        activityChartInstance.destroy();
        activityChartInstance = null;
    }
    
    // Criar novos gráficos
    setTimeout(() => {
        createStatusChart();
        createPlanChart();
        createActivityChart();
    }, 100);
};

onMounted(() => {
    initializeCharts();
});

// Watch para mudanças nos dados das licenças
watch(() => props.licenses, () => {
    initializeCharts();
}, { deep: true });

onUnmounted(() => {
    // Limpar gráficos ao desmontar o componente
    if (statusChartInstance) {
        statusChartInstance.destroy();
    }
    if (planChartInstance) {
        planChartInstance.destroy();
    }
    if (activityChartInstance) {
        activityChartInstance.destroy();
    }
});
</script>
