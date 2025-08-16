<template>
    <div class="w-full h-full flex flex-col">
        <div v-if="type === 'bar'" class="flex-1 flex items-end justify-between px-4">
            <div 
                v-for="(item, index) in data" 
                :key="index"
                class="flex flex-col items-center flex-1 mx-1"
            >
                <div class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                    {{ item.value }}
                </div>
                <div 
                    :style="`height: ${(item.value / maxValue) * 180}px`"
                    :class="getBarColor(index)"
                    class="w-full rounded-t transition-all duration-300"
                ></div>
                <div class="text-xs text-gray-600 dark:text-gray-400 mt-2">
                    {{ item.label }}
                </div>
            </div>
        </div>

        <div v-else-if="type === 'line'" class="flex-1 relative w-full">
            <svg class="w-full h-full" viewBox="0 0 400 300" preserveAspectRatio="none">
                <!-- Grid lines -->
                <g class="grid-lines">
                    <line 
                        v-for="i in 6" 
                        :key="`h-${i}`"
                        :y1="i * 50" 
                        :y2="i * 50" 
                        x1="0" 
                        x2="400" 
                        class="stroke-gray-200 dark:stroke-gray-700" 
                        stroke-width="1"
                    />
                    <line 
                        v-for="i in 8" 
                        :key="`v-${i}`"
                        :x1="i * 50" 
                        :x2="i * 50" 
                        y1="0" 
                        y2="300" 
                        class="stroke-gray-200 dark:stroke-gray-700" 
                        stroke-width="1"
                    />
                </g>
                <!-- Line path -->
                <path
                    :d="linePath"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="3"
                    class="text-primary-500"
                />
                <!-- Data points -->
                <circle
                    v-for="(point, index) in linePoints"
                    :key="index"
                    :cx="point.x"
                    :cy="point.y"
                    r="4"
                    class="fill-primary-500"
                />
            </svg>
        </div>

        <div v-else-if="type === 'donut'" class="flex-1 w-full flex flex-col items-center justify-center">
            <div class="relative w-full h-full flex items-center justify-center">
                <svg class="w-full h-full max-w-sm max-h-sm" viewBox="0 0 240 240" preserveAspectRatio="xMidYMid meet">
                    <circle
                        cx="120"
                        cy="120"
                        r="90"
                        fill="none"
                        class="stroke-gray-200 dark:stroke-gray-700"
                        stroke-width="20"
                    />
                    <circle
                        v-for="(segment, index) in donutSegments"
                        :key="index"
                        cx="120"
                        cy="120"
                        r="90"
                        fill="none"
                        :class="segment.color"
                        stroke-width="20"
                        :stroke-dasharray="segment.dashArray"
                        :stroke-dashoffset="segment.dashOffset"
                        class="transition-all duration-300 transform -rotate-90"
                    />
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ totalValue }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            Total
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legend for donut chart -->
        <div v-if="type === 'donut'" class="mt-4 flex flex-wrap justify-center gap-4">
            <div 
                v-for="(item, index) in data" 
                :key="index"
                class="flex items-center space-x-2"
            >
                <div :class="getBarColor(index)" class="w-3 h-3 rounded-full flex-shrink-0"></div>
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ item.label }} ({{ item.value }})
                </span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    type: {
        type: String,
        required: true,
        validator: (value) => ['bar', 'line', 'donut'].includes(value)
    },
    data: {
        type: Array,
        required: true
    },
    colors: {
        type: Array,
        default: () => [
            'bg-blue-500',
            'bg-green-500',
            'bg-purple-500',
            'bg-yellow-500',
            'bg-red-500',
            'bg-indigo-500',
            'bg-pink-500'
        ]
    }
});

const maxValue = computed(() => {
    return Math.max(...props.data.map(item => item.value));
});

const totalValue = computed(() => {
    return props.data.reduce((sum, item) => sum + item.value, 0);
});

const getBarColor = (index) => {
    const colorMap = {
        'bg-blue-500': 'bg-blue-500',
        'bg-green-500': 'bg-green-500', 
        'bg-purple-500': 'bg-purple-500',
        'bg-yellow-500': 'bg-yellow-500',
        'bg-red-500': 'bg-red-500',
        'bg-indigo-500': 'bg-indigo-500',
        'bg-pink-500': 'bg-pink-500'
    };
    
    const color = props.colors[index % props.colors.length];
    return colorMap[color] || 'bg-gray-500';
};

const linePath = computed(() => {
    if (props.type !== 'line' || props.data.length === 0) return '';
    
    const width = 400;
    const height = 300;
    const stepX = width / (props.data.length - 1);
    
    let path = '';
    
    props.data.forEach((point, index) => {
        const x = index * stepX;
        const y = height - (point.value / maxValue.value) * height;
        
        if (index === 0) {
            path += `M ${x} ${y}`;
        } else {
            path += ` L ${x} ${y}`;
        }
    });
    
    return path;
});

const linePoints = computed(() => {
    if (props.type !== 'line') return [];
    
    const width = 400;
    const height = 300;
    const stepX = width / (props.data.length - 1);
    
    return props.data.map((point, index) => ({
        x: index * stepX,
        y: height - (point.value / maxValue.value) * height
    }));
});

const donutSegments = computed(() => {
    if (props.type !== 'donut') return [];
    
    const circumference = 2 * Math.PI * 90; // radius = 90
    let currentOffset = 0;
    
    return props.data.map((item, index) => {
        const percentage = item.value / totalValue.value;
        const dashArray = `${percentage * circumference} ${circumference}`;
        const dashOffset = -currentOffset;
        
        currentOffset += percentage * circumference;
        
        return {
            dashArray,
            dashOffset,
            color: getStrokeColor(index)
        };
    });
});

const getStrokeColor = (index) => {
    const strokeColors = [
        'stroke-blue-500',
        'stroke-green-500',
        'stroke-purple-500',
        'stroke-yellow-500',
        'stroke-red-500',
        'stroke-indigo-500',
        'stroke-pink-500'
    ];
    
    return strokeColors[index % strokeColors.length];
};
</script>
