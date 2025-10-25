<script setup lang="ts">
import type { EmptyStateProps } from '@/types/components'

defineProps<EmptyStateProps>()
</script>

<template>
    <div class="text-center py-16 px-6">
        <div class="flex justify-center mb-6">
            <div class="relative">
                <div class="absolute inset-0 bg-gray-100 rounded-full blur-xl opacity-50"></div>
                <div class="relative flex items-center justify-center h-20 w-20 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 border border-gray-300">
                    <slot name="icon" />
                </div>
            </div>
        </div>
        <h3 class="text-xl font-semibold text-gray-900 mb-3">
            {{ title }}
        </h3>
        <p class="text-base text-gray-600 mb-8 max-w-sm mx-auto leading-relaxed">
            {{ description }}
        </p>
        <div>
            <slot name="action" />
        </div>
    </div>
</template>
