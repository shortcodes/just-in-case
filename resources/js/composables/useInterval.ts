import { computed, type ComputedRef } from 'vue'

/**
 * Parse ISO 8601 duration to days
 * Examples: P30D = 30, P90D = 90, P1Y = 365
 */
export function parseIntervalToDays(interval: string): number {
    if (!interval) return 0

    const isoMatch = interval.match(
        /^P(?:(\d+)Y)?(?:(\d+)M)?(?:(\d+)W)?(?:(\d+)D)?(?:T(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?)?$/
    )

    if (!isoMatch) {
        return 0
    }

    const [
        ,
        years,
        months,
        weeks,
        days,
        hours,
        minutes,
        seconds,
    ] = isoMatch

    const totalDays =
        (years ? parseInt(years, 10) * 365 : 0) +
        (months ? parseInt(months, 10) * 30 : 0) +
        (weeks ? parseInt(weeks, 10) * 7 : 0) +
        (days ? parseInt(days, 10) : 0) +
        (hours ? parseInt(hours, 10) / 24 : 0) +
        (minutes ? parseInt(minutes, 10) / (24 * 60) : 0) +
        (seconds ? parseInt(seconds, 10) / (24 * 60 * 60) : 0)

    return totalDays
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
