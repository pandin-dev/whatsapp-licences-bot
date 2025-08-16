<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gray-900 pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/">
                <div class="flex items-center space-x-2">
                    <div class="w-12 h-12 bg-primary-600 rounded-xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                        </svg>
                    </div>
                    <span class="text-2xl font-bold text-white">
                        QiBotZap
                    </span>
                </div>
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-gray-800 border border-gray-700 px-6 py-4 shadow-xl sm:max-w-md sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
