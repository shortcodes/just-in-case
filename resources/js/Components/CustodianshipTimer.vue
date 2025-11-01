<script setup lang="ts">
import { computed } from 'vue'
import TimerProgressBar from './TimerProgressBar.vue'
import { ClockIcon } from '@heroicons/vue/24/outline'
import { useTimerCountdown } from '@/composables/useTimerCountdown'
import { parseIntervalToDays } from '@/composables/useInterval'
import { useTrans } from '@/composables/useTrans'
import type { CustodianshipStatus } from '@/types/models'

interface Props {
    nextTriggerAt: string | null
    interval: string
    status: CustodianshipStatus
}

const props = defineProps<Props>()
const trans = useTrans()

const {
    detailedCountdown,
    isExpired,
} = useTimerCountdown(props.nextTriggerAt, props.interval)

const showTimer = computed(() => {
    return props.status === 'active' && !isExpired.value
})

const timerColorClass = computed(() => {
    if (isExpired.value) return 'text-red-700 font-semibold'
    return 'text-gray-700 font-medium'
})

const formatInterval = (interval: string) => {
    const intervalDays = parseIntervalToDays(interval)
    const totalMinutes = intervalDays * 24 * 60

    if (totalMinutes < 60) {
        const roundedMinutes = Math.max(1, Math.round(totalMinutes))
        const unit = roundedMinutes === 1 ? trans('minute') : trans('minutes')
        return `${roundedMinutes} ${unit}`
    }

    if (totalMinutes < 24 * 60) {
        const hours = Math.max(1, Math.round(totalMinutes / 60))
        const unit = hours === 1 ? trans('hour') : trans('hours')
        return `${hours} ${unit}`
    }

    const days = Math.max(1, Math.round(intervalDays))
    const unit = days === 1 ? trans('day') : trans('days')
    return `${days} ${unit}`
}

const formattedInterval = computed(() => formatInterval(props.interval))
</script>

<template>
    <div v-if="showTimer" class="space-y-3">
        <!-- Interval and Timer Display -->
        <div class="flex items-center justify-between">
            <!-- Interval on the left -->
            <div class="flex items-center gap-2 text-sm text-gray-600">
                <ClockIcon class="h-4 w-4" />
                <span>{{ trans(':interval interval').replace(':interval', formattedInterval) }}</span>
            </div>

            <!-- Countdown timer on the right -->
            <div class="text-right">
                <div class="text-xs text-gray-500 mb-1">{{ trans('Time remaining') }}</div>
                <div class="text-lg font-mono" :class="timerColorClass">
                    {{ detailedCountdown }}
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        <TimerProgressBar
            :next-trigger-at="nextTriggerAt"
            :interval="interval"
            :status="status"
        />
    </div>
</template>
