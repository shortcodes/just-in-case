/**
 * useTimerCountdown Composable
 *
 * Manages dynamic timer countdown with Page Visibility API support
 */

import { ref, computed, onMounted, onUnmounted, type Ref } from 'vue'
import dayjs from 'dayjs'
import duration from 'dayjs/plugin/duration'
import relativeTime from 'dayjs/plugin/relativeTime'

dayjs.extend(duration)
dayjs.extend(relativeTime)

export interface UseTimerCountdownReturn {
    daysRemaining: Ref<number>
    progressPercentage: Ref<number>
    colorClass: Ref<string>
    isExpired: Ref<boolean>
    formattedTimeRemaining: Ref<string>
    exactExpiryDate: Ref<string>
    detailedCountdown: Ref<string>
    totalSecondsRemaining: Ref<number>
}

export function useTimerCountdown(
    nextTriggerAt: string | null,
    intervalDays: number,
): UseTimerCountdownReturn {
    const now = ref(dayjs())
    let intervalId: number | null = null
    let isVisible = ref(true)

    const totalSecondsRemaining = computed(() => {
        if (!nextTriggerAt) return 0
        const diff = dayjs(nextTriggerAt).diff(now.value, 'second')
        return Math.max(0, diff)
    })

    const daysRemaining = computed(() => {
        if (!nextTriggerAt) return 0
        const diff = dayjs(nextTriggerAt).diff(now.value, 'day', true)
        return Math.max(0, Math.ceil(diff))
    })

    const progressPercentage = computed(() => {
        if (!nextTriggerAt || intervalDays === 0) return 0

        // Calculate based on total seconds for accuracy with short intervals
        const totalIntervalSeconds = intervalDays * 24 * 60 * 60
        const secondsRemaining = totalSecondsRemaining.value
        const percentage = (secondsRemaining / totalIntervalSeconds) * 100

        return Math.max(0, Math.min(100, percentage))
    })

    const colorClass = computed(() => {
        const days = daysRemaining.value
        if (days <= 0) return 'text-destructive'
        if (days < 7) return 'text-destructive'
        if (days < 30) return 'text-yellow-600'
        return 'text-green-600'
    })

    const isExpired = computed(() => {
        if (!nextTriggerAt) return false
        return dayjs(nextTriggerAt).isBefore(now.value)
    })

    const formattedTimeRemaining = computed(() => {
        if (!nextTriggerAt) return 'Timer inactive'
        if (isExpired.value) return 'Expired'

        const days = daysRemaining.value
        if (days === 0) return 'Less than 1 day'
        if (days === 1) return '1 day remaining'
        return `${days} days remaining`
    })

    const exactExpiryDate = computed(() => {
        if (!nextTriggerAt) return ''
        return dayjs(nextTriggerAt).format('MMMM D, YYYY HH:mm')
    })

    const detailedCountdown = computed(() => {
        if (!nextTriggerAt) return 'Timer inactive'
        if (isExpired.value) return 'Expired'

        const seconds = totalSecondsRemaining.value
        const days = Math.floor(seconds / (24 * 60 * 60))
        const hours = Math.floor((seconds % (24 * 60 * 60)) / (60 * 60))
        const minutes = Math.floor((seconds % (60 * 60)) / 60)
        const secs = seconds % 60

        if (days > 0) {
            return `${days}d ${hours}h ${minutes}m ${secs}s`
        } else if (hours > 0) {
            return `${hours}h ${minutes}m ${secs}s`
        } else if (minutes > 0) {
            return `${minutes}m ${secs}s`
        } else {
            return `${secs}s`
        }
    })

    const updateTime = () => {
        if (isVisible.value) {
            now.value = dayjs()
        }
    }

    const handleVisibilityChange = () => {
        if (document.hidden) {
            isVisible.value = false
        } else {
            isVisible.value = true
            now.value = dayjs()
        }
    }

    onMounted(() => {
        intervalId = window.setInterval(updateTime, 1000)
        document.addEventListener('visibilitychange', handleVisibilityChange)
    })

    onUnmounted(() => {
        if (intervalId !== null) {
            clearInterval(intervalId)
        }
        document.removeEventListener('visibilitychange', handleVisibilityChange)
    })

    return {
        daysRemaining,
        progressPercentage,
        colorClass,
        isExpired,
        formattedTimeRemaining,
        exactExpiryDate,
        detailedCountdown,
        totalSecondsRemaining,
    }
}
