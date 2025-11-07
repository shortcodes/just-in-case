<script setup lang="ts">
import { computed } from 'vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import CustodianshipTimer from './CustodianshipTimer.vue'
import { useTrans } from '@/composables/useTrans'
import { useDateFormat } from '@/composables/useDateFormat'
import type { CustodianshipDetailViewModel } from '@/types/models'
import dayjs from '@/plugins/dayjs'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'

interface TimerSectionProps {
    custodianship: CustodianshipDetailViewModel
}

const props = defineProps<TimerSectionProps>()
const trans = useTrans()
const { formatDate } = useDateFormat()

const statusDisplay = computed(() => {
    if (props.custodianship.status === 'draft') {
        return {
            text: trans('Timer inactive'),
            class: 'text-gray-500',
            bgClass: 'bg-gray-50 border-gray-200'
        }
    }

    if (props.custodianship.status === 'completed') {
        const deliveredDate = props.custodianship.updatedAt
            ? formatDate(props.custodianship.updatedAt, false)
            : 'Unknown'
        return {
            text: trans('Message sent on :date').replace(':date', deliveredDate),
            class: 'text-blue-700',
            bgClass: 'bg-blue-50 border-blue-200'
        }
    }

    const isExpired = props.custodianship.nextTriggerAt
        ? dayjs(props.custodianship.nextTriggerAt).isBefore(dayjs())
        : false

    if (isExpired) {
        return {
            text: trans('Timer expired. Message will be sent shortly.'),
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
    return formatDate(props.custodianship.lastResetAt, true)
})

const nextTriggerText = computed(() => {
    if (!props.custodianship.nextTriggerAt) return null
    return formatDate(props.custodianship.nextTriggerAt, true)
})
</script>

<template>
    <Card data-testid="timer-section">
        <CardHeader>
            <CardTitle>{{ trans('Timer') }}</CardTitle>
        </CardHeader>
        <CardContent class="space-y-4">
            <!-- Timer Display (interval + countdown + progress bar) -->
            <CustodianshipTimer
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
                    <span class="font-medium">{{ trans('Last reset:') }}</span>
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
                    <span class="font-medium">{{ trans('Next trigger:') }}</span>
                    <span class="text-gray-900">{{ nextTriggerText }}</span>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
