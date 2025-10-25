import { computed, type ComputedRef } from 'vue'

/**
 * Parse ISO 8601 duration to days
 * Examples: P30D = 30, P90D = 90, P1Y = 365
 */
export function parseIntervalToDays(interval: string): number {
    if (!interval) return 0

    // Simple parser for common ISO 8601 durations
    const dayMatch = interval.match(/P(\d+)D/)
    if (dayMatch) {
        return parseInt(dayMatch[1], 10)
    }

    const yearMatch = interval.match(/P(\d+)Y/)
    if (yearMatch) {
        return parseInt(yearMatch[1], 10) * 365
    }

    const monthMatch = interval.match(/P(\d+)M/)
    if (monthMatch) {
        return parseInt(monthMatch[1], 10) * 30
    }

    return 0
}

/**
 * Composable to work with interval durations
 */
export function useInterval(interval: string) {
    const days = computed(() => parseIntervalToDays(interval))

    const formatted = computed(() => {
        const totalDays = days.value

        if (totalDays >= 365) {
            const years = Math.floor(totalDays / 365)
            return years === 1 ? '1 year' : `${years} years`
        }

        if (totalDays >= 30) {
            const months = Math.floor(totalDays / 30)
            return months === 1 ? '1 month' : `${months} months`
        }

        return totalDays === 1 ? '1 day' : `${totalDays} days`
    })

    return {
        days,
        formatted,
    }
}
