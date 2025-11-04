<script setup lang="ts">
import { computed } from 'vue'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import {
    CheckCircleIcon,
    XCircleIcon,
    ClockIcon,
    ExclamationTriangleIcon,
    ArrowPathIcon
} from '@heroicons/vue/24/outline'
import { useTrans } from '@/composables/useTrans'
import dayjs from '@/plugins/dayjs'
import type { RecipientViewModel } from '@/types/models'

interface Props {
    recipient: RecipientViewModel
    showDeliveryStatus: boolean
}

const props = defineProps<Props>()
const trans = useTrans()

type RecipientDeliveryStatus = 'pending' | 'delivered' | 'failed'

const deliveryStatus = computed<RecipientDeliveryStatus>(() => {
    return (props.recipient.latestDelivery?.status ?? 'pending') as RecipientDeliveryStatus
})

const statusConfig = computed(() => {
    switch (deliveryStatus.value) {
        case 'delivered':
            return {
                label: trans('Delivered'),
                icon: CheckCircleIcon,
                badgeClass: 'bg-green-100 text-green-800 hover:bg-green-100',
                iconClass: 'text-green-600',
                cardBgClass: 'bg-green-50/30'
            }
        case 'failed':
            return {
                label: trans('Delivery Failed'),
                icon: XCircleIcon,
                badgeClass: 'bg-red-100 text-red-800 hover:bg-red-100',
                iconClass: 'text-red-600',
                cardBgClass: 'bg-red-50/30'
            }
        default:
            return {
                label: trans('Pending Delivery'),
                icon: ClockIcon,
                badgeClass: 'bg-yellow-100 text-yellow-800 hover:bg-yellow-100',
                iconClass: 'text-yellow-600',
                cardBgClass: 'bg-yellow-50/30'
            }
    }
})

const hasDeliveryInfo = computed(() => {
    return props.recipient.latestDelivery && props.showDeliveryStatus
})

const statusUpdatedAt = computed(() => {
    if (deliveryStatus.value === 'pending') return null
    const updatedAt = props.recipient.latestDelivery?.updatedAt
    if (!updatedAt) return null
    return dayjs(updatedAt).fromNow()
})

const isRetrying = computed(() => {
    return props.recipient.latestDelivery?.status === 'pending' &&
           (props.recipient.latestDelivery?.attemptNumber || 0) > 1
})

const isFinalAttempt = computed(() => {
    const attemptNumber = props.recipient.latestDelivery?.attemptNumber || 1
    const maxAttempts = props.recipient.latestDelivery?.maxAttempts || 3
    return attemptNumber === maxAttempts
})

const attemptText = computed(() => {
    const current = props.recipient.latestDelivery?.attemptNumber || 1
    const max = props.recipient.latestDelivery?.maxAttempts || 3
    return trans('custodianships.delivery.attempt')
        .replace(':current', current.toString())
        .replace(':max', max.toString())
})

const nextRetryTime = computed(() => {
    const nextRetryAt = props.recipient.latestDelivery?.nextRetryAt
    if (!nextRetryAt) return null
    return dayjs(nextRetryAt).format('L LT')
})
</script>

<template>
    <div
        :class="[
            'rounded-lg shadow-sm p-4 border',
            hasDeliveryInfo && showDeliveryStatus ? statusConfig.cardBgClass : 'bg-white'
        ]"
    >
            <!-- Header: Email and Status -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                <!-- Email -->
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">
                        {{ recipient.email }}
                    </p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ trans('Added :time').replace(':time', dayjs(recipient.createdAt).fromNow()) }}
                    </p>
                </div>

                <!-- Status Badge with Icon -->
                <div v-if="showDeliveryStatus" class="flex flex-wrap items-center gap-2">
                    <component
                        :is="statusConfig.icon"
                        :class="['h-5 w-5', statusConfig.iconClass]"
                    />
                    <Badge
                        :class="[
                            'font-medium',
                            statusConfig.badgeClass
                        ]"
                    >
                        {{ statusConfig.label }}
                    </Badge>
                </div>
            </div>

            <!-- Updated Time (for delivered/failed) -->
            <div v-if="statusUpdatedAt && showDeliveryStatus" class="mb-3">
                <p class="text-xs text-gray-500 flex items-center gap-1.5">
                    <ClockIcon class="h-3.5 w-3.5" />
                    {{ trans('Updated :time').replace(':time', statusUpdatedAt) }}
                </p>
            </div>

            <!-- Delivery Details (for pending status) -->
            <template v-if="hasDeliveryInfo && recipient.latestDelivery!.status === 'pending'">
                <Separator class="my-3" />

                <div class="space-y-3">
                    <!-- Attempt Information -->
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Retry Indicator -->
                        <div
                            v-if="isRetrying"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium bg-blue-100 text-blue-900"
                        >
                            <ArrowPathIcon class="h-3.5 w-3.5" />
                            <span>{{ trans('Retrying') }}</span>
                        </div>

                        <!-- Attempt Number -->
                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium bg-yellow-100 text-yellow-900">
                            <span>{{ attemptText }}</span>
                        </div>

                        <!-- Final Attempt Warning -->
                        <div
                            v-if="isFinalAttempt && !recipient.latestDelivery!.nextRetryAt"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-semibold bg-orange-100 text-orange-900"
                        >
                            <ExclamationTriangleIcon class="h-3.5 w-3.5" />
                            <span>{{ trans('custodianships.delivery.final_attempt') }}</span>
                        </div>

                        <!-- Next Retry Time -->
                        <div
                            v-if="nextRetryTime"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium bg-indigo-100 text-indigo-900"
                        >
                            <ClockIcon class="h-3.5 w-3.5" />
                            <span>{{ trans('custodianships.delivery.next_retry_at').replace(':time', nextRetryTime) }}</span>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div
                        v-if="recipient.latestDelivery!.errorMessage"
                        class="p-3 rounded-md bg-red-100"
                    >
                        <div class="flex gap-2">
                            <XCircleIcon class="h-4 w-4 text-red-600 shrink-0 mt-0.5" />
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-red-900 mb-1">
                                    {{ trans('custodianships.delivery.error') }}
                                </p>
                                <p class="text-xs text-red-700 break-words">
                                    {{ recipient.latestDelivery!.errorMessage }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

            <!-- Error Message (for failed status) -->
            <template v-else-if="hasDeliveryInfo && deliveryStatus === 'failed' && recipient.latestDelivery!.errorMessage">
                <Separator class="my-3" />

                <div class="p-3 rounded-md bg-red-100">
                    <div class="flex gap-2">
                        <XCircleIcon class="h-4 w-4 text-red-600 shrink-0 mt-0.5" />
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-red-900 mb-1">
                                {{ trans('custodianships.delivery.error') }}
                            </p>
                            <p class="text-xs text-red-700 break-words">
                                {{ recipient.latestDelivery!.errorMessage }}
                            </p>
                        </div>
                    </div>
                </div>
            </template>
    </div>
</template>
