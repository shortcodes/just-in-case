<script setup lang="ts">
import { computed } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import TimerProgressBar from './TimerProgressBar.vue'
import type { CustodianshipDetailViewModel } from '@/types/models'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import { parseIntervalToDays } from '@/composables/useInterval'

dayjs.extend(relativeTime)

interface TimerSectionProps {
    custodianship: CustodianshipDetailViewModel
}

const props = defineProps<TimerSectionProps>()

const statusDisplay = computed(() => {
    if (props.custodianship.status === 'draft') {
        return {
            text: 'Timer inactive',
            class: 'text-gray-500',
            bgClass: 'bg-gray-50 border-gray-200'
        }
    }

    if (props.custodianship.status === 'completed') {
        const deliveredDate = props.custodianship.updatedAt
            ? dayjs(props.custodianship.updatedAt).format('MMMM D, YYYY')
            : 'Unknown'
        return {
            text: `Message sent on ${deliveredDate}`,
            class: 'text-blue-700',
            bgClass: 'bg-blue-50 border-blue-200'
        }
    }

    const isExpired = props.custodianship.nextTriggerAt
        ? dayjs(props.custodianship.nextTriggerAt).isBefore(dayjs())
        : false

    if (isExpired) {
        return {
            text: 'Timer expired. Message will be sent shortly.',
            class: 'text-amber-700 font-medium',
            bgClass: 'bg-amber-50 border-amber-200'
        }
    }

    return null
})

const lastResetText = computed(() => {
    if (!props.custodianship.lastResetAt) return null
    return dayjs(props.custodianship.lastResetAt).fromNow()
})

const lastResetTooltip = computed(() => {
    if (!props.custodianship.lastResetAt) return null
    return dayjs(props.custodianship.lastResetAt).format('MMMM D, YYYY h:mm A')
})

const nextTriggerText = computed(() => {
    if (!props.custodianship.nextTriggerAt) return null
    return dayjs(props.custodianship.nextTriggerAt).format('MMMM D, YYYY h:mm A')
})

const timerExpiresIn = computed(() => {
    if (!props.custodianship.nextTriggerAt) return null
    const days = dayjs(props.custodianship.nextTriggerAt).diff(dayjs(), 'day')
    if (days < 0) return 'Expired'
    if (days === 0) return 'Less than 1 day'
    return `${days} ${days === 1 ? 'day' : 'days'}`
})

const formattedInterval = computed(() => {
    const intervalDays = parseIntervalToDays(props.custodianship.interval)
    const totalMinutes = intervalDays * 24 * 60

    if (totalMinutes < 60) {
        const minutes = Math.max(1, Math.round(totalMinutes))
        return `${minutes} minute${minutes === 1 ? '' : 's'}`
    }

    if (totalMinutes < 24 * 60) {
        const hours = Math.max(1, Math.round(totalMinutes / 60))
        return `${hours} hour${hours === 1 ? '' : 's'}`
    }

    const days = Math.max(1, Math.round(intervalDays))
    return `${days} day${days === 1 ? '' : 's'}`
})
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Timer</CardTitle>
        </CardHeader>
        <CardContent class="space-y-4">
            <TimerProgressBar
                :next-trigger-at="custodianship.nextTriggerAt"
                :interval="custodianship.interval"
                :status="custodianship.status"
            />

            <div v-if="statusDisplay" :class="['p-3 rounded-md border', statusDisplay.bgClass]">
                <p :class="['text-sm', statusDisplay.class]">
                    {{ statusDisplay.text }}
                </p>
            </div>

            <div v-if="custodianship.status === 'active' && !statusDisplay" class="space-y-2 text-sm text-gray-600">
                <div v-if="lastResetText" class="flex items-center justify-between">
                    <span class="font-medium">Last reset:</span>
                    <TooltipProvider>
                        <Tooltip>
                            <TooltipTrigger as-child>
                                <span class="text-gray-900 cursor-help">{{ lastResetText }}</span>
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>{{ lastResetTooltip }}</p>
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>
                </div>

                <div v-if="nextTriggerText" class="flex items-center justify-between">
                    <span class="font-medium">Next trigger:</span>
                    <span class="text-gray-900">{{ nextTriggerText }}</span>
                </div>

                <div class="flex items-center justify-between">
                    <span class="font-medium">Check-in interval:</span>
                    <span class="text-gray-900">{{ formattedInterval }}</span>
                </div>

                <div v-if="timerExpiresIn && timerExpiresIn !== 'Expired'" class="flex items-center justify-between">
                    <span class="font-medium">Timer expires in:</span>
                    <span class="text-gray-900">{{ timerExpiresIn }}</span>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
