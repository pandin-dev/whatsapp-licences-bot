<template>
    <svg :class="className" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <!-- Shield Check -->
        <path v-if="name === 'shield-check'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        
        <!-- Plus Circle -->
        <path v-else-if="name === 'plus-circle'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
        
        <!-- Arrow Path -->
        <path v-else-if="name === 'arrow-path'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        
        <!-- X Circle -->
        <path v-else-if="name === 'x-circle'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        
        <!-- Clock -->
        <path v-else-if="name === 'clock'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        
        <!-- Information Circle -->
        <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
</template>

<script setup>
const props = defineProps({
    name: {
        type: String,
        required: true
    },
    className: {
        type: String,
        default: 'w-4 h-4'
    }
});
</script>
