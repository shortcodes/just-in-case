<script setup lang="ts">
import { computed, ref } from 'vue'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Link } from '@inertiajs/vue3'
import { ClockIcon, UserGroupIcon, ArrowPathIcon, PaperClipIcon, InformationCircleIcon } from '@heroicons/vue/24/outline'
import StatusBadge from './StatusBadge.vue'
import TimerProgressBar from './TimerProgressBar.vue'
import ConfirmableButton from './ConfirmableButton.vue'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import { useTimerCountdown } from '@/composables/useTimerCountdown'
import { parseIntervalToDays } from '@/composables/useInterval'
import type { CustodianshipCardProps } from '@/types/components'
import dayjs from 'dayjs'

const props = withDefaults(defineProps<CustodianshipCardProps>(), {
    isResetting: false,
    emailVerified: false,
})

const emailTooltipOpen = ref(false)
const recipientMessageTooltipOpen = ref(false)

const emit = defineEmits<{
    reset: [custodianshipUuid: string]
    activate: [custodianshipUuid: string]
}>()

const {
    detailedCountdown,
    isExpired,
} = useTimerCountdown(props.custodianship.nextTriggerAt, props.custodianship.interval)

const isInactive = computed(() => {
    return props.custodianship.status === 'draft' || props.custodianship.status === 'completed'
})

const canReset = computed(() => {
    return props.custodianship.status === 'active' && !isExpired.value
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

const handleReset = () => {
    emit('reset', props.custodianship.uuid)
}

const handleActivate = () => {
    emit('activate', props.custodianship.uuid)
}

const toggleEmailTooltip = () => {
    emailTooltipOpen.value = !emailTooltipOpen.value
}

const toggleRecipientMessageTooltip = () => {
    recipientMessageTooltipOpen.value = !recipientMessageTooltipOpen.value
}

const isDraft = computed(() => {
    return props.custodianship.status === 'draft'
})

const hasRecipients = computed(() => {
    return props.custodianship.recipientsCount && props.custodianship.recipientsCount > 0
})

const hasMessage = computed(() => {
    return props.custodianship.messageContent && props.custodianship.messageContent.trim().length > 0
})

const canActivate = computed(() => {
    return isDraft.value && props.emailVerified && hasRecipients.value && hasMessage.value
})

const missingEmailVerification = computed(() => {
    return isDraft.value && !props.emailVerified
})

const missingRecipientOrMessage = computed(() => {
    return isDraft.value && (!hasRecipients.value || !hasMessage.value)
})

const showTimer = computed(() => {
    // Only show the live countdown while active and not yet expired
    return props.custodianship.status === 'active' && !isExpired.value
})

const timerColorClass = computed(() => {
    if (isExpired.value) return 'text-red-700 font-semibold'
    return 'text-gray-700 font-medium'
})

const formatInterval = (interval: string) => {
    const intervalDays = parseIntervalToDays(interval)
    const totalMinutes = intervalDays * 24 * 60

    if (totalMinutes < 60) {
        // Less than 1 hour - show in minutes
        return `${Math.round(totalMinutes)} minute interval`
    } else if (totalMinutes < 24 * 60) {
        // Less than 1 day - show in hours
        const hours = totalMinutes / 60
        return `${Math.round(hours)} hour interval`
    } else {
        // 1 day or more - show in days
        const days = Math.round(intervalDays)
        return `${days} day interval`
    }
}

// Display status aligns with Show page: once expired, show as pending
const displayStatus = computed(() => {
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
                        <div class="flex items-center gap-2 mt-1 text-sm" :class="subtextColorClass">
                            <ClockIcon class="h-4 w-4" />
                            <span>{{ formatInterval(custodianship.interval) }}</span>
                        </div>
                    </div>

                    <!-- Show timer for active, status badge for others -->
                    <div v-if="showTimer" class="text-right">
                        <div class="text-xs text-gray-500 mb-1">Time remaining</div>
                        <div class="text-lg font-mono" :class="timerColorClass">
                            {{ detailedCountdown }}
                        </div>
                    </div>
                    <StatusBadge
                        v-else
                        :status="displayStatus"
                        :delivery-status="custodianship.deliveryStatus"
                    />
                </div>

                <!-- Timer Progress -->
                <div class="pt-2">
                    <TimerProgressBar
                        :next-trigger-at="custodianship.nextTriggerAt"
                        :interval="custodianship.interval"
                        :status="custodianship.status"
                    />
                </div>

                <!-- Recipients and Attachments -->
                <div class="flex items-center gap-4 text-sm" :class="subtextColorClass">
                    <div class="flex items-center gap-2">
                        <UserGroupIcon class="h-4 w-4" />
                        <span>
                            {{ custodianship.recipientsCount ?? 0 }} {{ custodianship.recipientsCount === 1 ? 'recipient' : 'recipients' }}
                        </span>
                    </div>
                    <div v-if="custodianship.attachmentsCount && custodianship.attachmentsCount > 0" class="flex items-center gap-2">
                        <PaperClipIcon class="h-4 w-4" />
                        <span>
                            {{ custodianship.attachmentsCount }} {{ custodianship.attachmentsCount === 1 ? 'attachment' : 'attachments' }}
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2 pt-2 border-t">
                    <!-- Activate button for drafts -->
                    <ConfirmableButton
                        v-if="isDraft"
                        label="Activate"
                        confirm-label="Confirm Activation"
                        size="default"
                        :disabled="!canActivate"
                        :button-class="canActivate ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-gray-400 text-gray-200 cursor-not-allowed'"
                        confirm-button-class="bg-green-600 hover:bg-green-700 text-white"
                        @confirm="handleActivate"
                    />

                    <!-- Info icon for email verification -->
                    <TooltipProvider v-if="missingEmailVerification" :delayDuration="0">
                        <Tooltip :open="emailTooltipOpen" @update:open="emailTooltipOpen = $event">
                            <TooltipTrigger as-child>
                                <InformationCircleIcon
                                    class="h-5 w-5 text-red-500 cursor-pointer outline-none focus:outline-none select-none hover:text-red-600"
                                    @click="toggleEmailTooltip"
                                    tabindex="0"
                                />
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>Please confirm your email address to activate</p>
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>

                    <!-- Info icon for recipient/message requirement -->
                    <TooltipProvider v-if="missingRecipientOrMessage" :delayDuration="0">
                        <Tooltip :open="recipientMessageTooltipOpen" @update:open="recipientMessageTooltipOpen = $event">
                            <TooltipTrigger as-child>
                                <InformationCircleIcon
                                    class="h-5 w-5 text-red-500 cursor-pointer outline-none focus:outline-none select-none hover:text-red-600"
                                    @click="toggleRecipientMessageTooltip"
                                    tabindex="0"
                                />
                            </TooltipTrigger>
                            <TooltipContent>
                                <p>You have to have recipient and message to activate this custodianship</p>
                            </TooltipContent>
                        </Tooltip>
                    </TooltipProvider>

                    <!-- Reset button for active custodianships -->
                    <ConfirmableButton
                        v-if="canReset"
                        label="Reset Timer"
                        size="default"
                        :disabled="!canReset || isResetting"
                        :tooltip-disabled="isExpired ? 'Cannot reset - message will be sent shortly' : 'Timer can only be reset for active custodianships'"
                        @confirm="handleReset"
                    >
                        <template #icon>
                            <ArrowPathIcon class="h-4 w-4 mr-1.5" />
                        </template>
                    </ConfirmableButton>

                    <div class="ml-auto flex gap-2">
                        <Link :href="route('custodianships.show', custodianship.uuid)">
                            <Button variant="ghost" size="sm" class="text-gray-600 hover:text-gray-900">
                                View Details
                            </Button>
                        </Link>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
