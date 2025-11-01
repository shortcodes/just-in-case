<script setup lang="ts">
import { computed } from 'vue'
import { Progress } from '@/components/ui/progress'
import { useTrans } from '@/composables/useTrans'

interface Props {
    usedSize: number // bytes
    maxSize?: number // bytes
}

const props = withDefaults(defineProps<Props>(), {
    maxSize: 10485760, // 10MB
})

const trans = useTrans()

const percentage = computed(() => {
    return Math.round((props.usedSize / props.maxSize) * 100)
})

const colorClass = computed(() => {
    const pct = percentage.value
    if (pct < 70) return 'bg-green-600'
    if (pct < 90) return 'bg-yellow-600'
    return 'bg-red-600'
})

const formatBytes = (bytes: number): string => {
    if (bytes === 0) return '0 MB'
    const mb = bytes / 1048576
    return `${mb.toFixed(2)} MB`
}

const formattedUsed = computed(() => formatBytes(props.usedSize))
const formattedMax = computed(() => formatBytes(props.maxSize))
</script>

<template>
    <div class="space-y-2">
        <div class="flex justify-between text-sm">
            <span class="text-gray-700">{{ trans('Storage used') }}</span>
            <span
                class="font-medium"
                :class="{
                    'text-green-700': percentage < 70,
                    'text-yellow-700': percentage >= 70 && percentage < 90,
                    'text-red-700': percentage >= 90
                }"
            >
                {{ formattedUsed }} / {{ formattedMax }}
            </span>
        </div>
        <div class="relative">
            <Progress
                :model-value="percentage"
                class="h-2"
            />
            <div
                class="absolute inset-0 rounded-full overflow-hidden"
                :style="{ width: `${Math.min(percentage, 100)}%` }"
            >
                <div
                    class="h-full transition-colors"
                    :class="colorClass"
                />
            </div>
        </div>
    </div>
</template>
