<script setup lang="ts">
import { computed } from 'vue'
import { Progress } from '@/components/ui/progress'
import type { TimerProgressBarProps } from '@/types/components'
import { useTimerCountdown } from '@/composables/useTimerCountdown'

const props = defineProps<TimerProgressBarProps>()

const {
    daysRemaining,
    progressPercentage,
    colorClass,
    isExpired,
    formattedTimeRemaining,
    exactExpiryDate,
    detailedCountdown,
    totalSecondsRemaining,
} = useTimerCountdown(props.nextTriggerAt, props.intervalDays)

const progressBarColor = computed(() => {
    if (props.status === 'draft') return 'bg-gray-400'
    if (isExpired.value || daysRemaining.value < 7) return 'bg-red-500'
    if (daysRemaining.value < 30) return 'bg-yellow-500'
    return 'bg-green-500'
})

const formatInterval = (intervalDays: number) => {
    const totalMinutes = intervalDays * 24 * 60

    if (totalMinutes < 60) {
        return `${Math.round(totalMinutes)} minutes`
    } else if (totalMinutes < 24 * 60) {
        const hours = totalMinutes / 60
        return `${Math.round(hours)} hours`
    } else {
        const days = Math.round(intervalDays)
        return `${days} days`
    }
}

const formatRemaining = (seconds: number) => {
    const minutes = seconds / 60
    const hours = minutes / 60
    const days = hours / 24

    if (minutes < 60) {
        return `${Math.ceil(minutes)} minutes`
    } else if (hours < 24) {
        return `${Math.ceil(hours)} hours`
    } else {
        return `${Math.ceil(days)} days`
    }
}

const displayText = computed(() => {
    if (props.status === 'draft') return 'Timer inactive'
    if (props.status === 'completed') return 'Completed'
    if (!props.nextTriggerAt) return 'Timer inactive'

    if (isExpired.value) return 'Expired - Message will be sent soon'

    const remaining = formatRemaining(totalSecondsRemaining.value)
    const interval = formatInterval(props.intervalDays)

    return `${remaining} of ${interval} remaining`
})

const textColorClass = computed(() => {
    if (props.status === 'draft' || props.status === 'completed') return 'text-gray-500'
    if (isExpired.value) return 'text-red-700 font-semibold'
    if (daysRemaining.value < 7) return 'text-red-600 font-medium'
    if (daysRemaining.value < 30) return 'text-yellow-700'
    return 'text-gray-700'
})
</script>

<template>
    <div class="space-y-2">
        <Progress
            :model-value="status === 'draft' || status === 'completed' ? 0 : progressPercentage"
            :class="[
                'h-2.5 transition-all duration-300',
                status === 'draft' || status === 'completed' ? '[&>div]:bg-gray-300' : '',
                progressPercentage >= 66 ? '[&>div]:bg-green-500' : '',
                progressPercentage >= 33 && progressPercentage < 66 ? '[&>div]:bg-yellow-500' : '',
                progressPercentage < 33 ? '[&>div]:bg-red-500' : '',
            ]"
        />
        <!-- Only show text for inactive/expired states, active shows timer in card header -->
        <div v-if="status !== 'active' || isExpired" class="flex items-center justify-between">
            <p :class="['text-sm text-left', textColorClass]">
                {{ displayText }}
            </p>
        </div>
    </div>
</template>
