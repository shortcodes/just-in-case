<script setup lang="ts">
import { computed, ref } from 'vue'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Link } from '@inertiajs/vue3'
import { ClockIcon, UserGroupIcon, PaperClipIcon } from '@heroicons/vue/24/outline'
import StatusBadge from './StatusBadge.vue'
import CustodianshipActions from './CustodianshipActions.vue'
import CustodianshipTimer from './CustodianshipTimer.vue'
import { useTimerCountdown } from '@/composables/useTimerCountdown'
import { parseIntervalToDays } from '@/composables/useInterval'
import { useTrans } from '@/composables/useTrans'
import type { CustodianshipCardProps } from '@/types/components'
import dayjs from '@/plugins/dayjs'

const props = withDefaults(defineProps<CustodianshipCardProps>(), {
    emailVerified: false,
})

const trans = useTrans()

const {
    isExpired,
} = useTimerCountdown(props.custodianship.nextTriggerAt, props.custodianship.interval)

const isInactive = computed(() => {
    return props.custodianship.status === 'draft' || props.custodianship.status === 'completed'
})

const cardClass = computed(() => {
    // Completed custodianships - white bg with colored left border based on delivery status
    if (props.custodianship.status === 'completed') {
        const deliveryStatus = props.custodianship.deliveryStatus
        if (deliveryStatus === 'delivered') {
            return 'border-l-4 border-l-green-500 bg-white hover:shadow-md transition-shadow duration-200'
        }
        if (deliveryStatus === 'failed' || deliveryStatus === 'bounced') {
            return 'border-l-4 border-l-red-500 bg-white hover:shadow-md transition-shadow duration-200'
        }
        // Pending delivery (status completed but no delivery status yet)
        return 'border-l-4 border-l-yellow-500 bg-white hover:shadow-md transition-shadow duration-200'
    }

    if (isInactive.value) {
        return 'bg-gray-50 border-gray-200 hover:shadow-sm transition-shadow duration-200 opacity-80'
    }
    return 'bg-white hover:shadow-md transition-shadow duration-200'
})

const textColorClass = computed(() => {
    if (props.custodianship.status === 'completed') return 'text-gray-900'
    if (isInactive.value) return 'text-gray-500'
    return 'text-gray-900'
})

const subtextColorClass = computed(() => {
    if (props.custodianship.status === 'completed') return 'text-gray-500'
    if (isInactive.value) return 'text-gray-400'
    return 'text-gray-500'
})


const displayStatus = computed(() => {
    if (props.custodianship.status === 'completed') {
        return 'completed'
    }
    return isExpired.value ? 'pending' : props.custodianship.status
})
</script>

<template>
    <Card :class="cardClass">
        <CardContent class="p-6">
            <div class="space-y-4">
                <!-- Header -->
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-semibold truncate" :class="textColorClass">
                            {{ custodianship.name }}
                        </h3>
                    </div>

                    <!-- Status badge for non-active states -->
                    <StatusBadge
                        v-if="custodianship.status !== 'active' || isExpired"
                        :status="displayStatus"
                        :delivery-status="custodianship.deliveryStatus"
                    />
                </div>

                <!-- Timer Section (interval + countdown + progress bar) -->
                <CustodianshipTimer
                    :next-trigger-at="custodianship.nextTriggerAt"
                    :interval="custodianship.interval"
                    :status="custodianship.status"
                />

                <!-- Recipients and Attachments -->
                <div class="flex items-center gap-4 text-sm" :class="subtextColorClass">
                    <div class="flex items-center gap-2">
                        <UserGroupIcon class="h-4 w-4" />
                        <span>
                            {{ custodianship.recipientsCount ?? 0 }} {{ custodianship.recipientsCount === 1 ? trans('recipient') : trans('recipients') }}
                        </span>
                    </div>
                    <div v-if="custodianship.attachmentsCount && custodianship.attachmentsCount > 0" class="flex items-center gap-2">
                        <PaperClipIcon class="h-4 w-4" />
                        <span>
                            {{ custodianship.attachmentsCount }} {{ custodianship.attachmentsCount === 1 ? trans('attachment') : trans('attachments') }}
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2 pt-2 border-t">
                    <CustodianshipActions
                        :custodianship="custodianship"
                        :email-verified="emailVerified"
                    />

                    <div class="ml-auto flex gap-2">
                        <Link :href="route('custodianships.show', custodianship.uuid)">
                            <Button variant="ghost" size="sm" class="text-gray-600 hover:text-gray-900">
                                {{ trans('View Details') }}
                            </Button>
                        </Link>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
