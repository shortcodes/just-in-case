<script setup lang="ts">
import { ref, computed } from 'vue'
import {Head, router} from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import StatusBadge from '@/Components/StatusBadge.vue'
import TimerSection from '@/Components/TimerSection.vue'
import MessageContentViewer from '@/Components/MessageContentViewer.vue'
import AttachmentList from '@/Components/AttachmentList.vue'
import ResetHistoryTable from '@/Components/ResetHistoryTable.vue'
import DeleteCustodianshipModal from '@/Components/DeleteCustodianshipModal.vue'
import DangerZone from '@/Components/DangerZone.vue'
import CustodianshipActions from '@/Components/CustodianshipActions.vue'
import CustodianshipTimer from '@/Components/CustodianshipTimer.vue'
import { PencilIcon, ChevronDownIcon, ChevronUpIcon, EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline'
import type { RecipientViewModel, ShowCustodianshipPageProps } from '@/types/models'
import { parseIntervalToDays } from '@/composables/useInterval'
import { useTrans } from '@/composables/useTrans'
import dayjs from '@/plugins/dayjs'

const props = defineProps<ShowCustodianshipPageProps>()
const trans = useTrans()

const custodianship = ref(props.custodianship)
const resetHistory = ref(props.resetHistory || [])

const isDeleting = ref(false)
const isDeleteModalOpen = ref(false)
const isHistoryExpanded = ref(false)
const isMessageVisible = ref(false)

const isExpired = computed(() => {
    if (!custodianship.value.nextTriggerAt) return false
    return dayjs(custodianship.value.nextTriggerAt).isBefore(dayjs())
})

const statusDisplay = computed(() => {
    if (isExpired.value) return 'pending'
    return custodianship.value.status
})

const breadcrumbs = computed(() => [
    { name: trans('Custodianships'), href: route('custodianships.index') },
    { name: custodianship.value.name, href: '#', current: true }
])

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

const formattedInterval = computed(() => formatInterval(custodianship.value.interval))

const handleEdit = () => {
    router.visit(route('custodianships.edit', custodianship.value.uuid))
}

const handleDelete = async () => {
    isDeleting.value = true

    try {
        await router.delete(route('custodianships.destroy', custodianship.value.uuid), {
            onSuccess: () => {
                router.visit(route('custodianships.index'))
            },
            onError: () => {
                isDeleteModalOpen.value = false
            }
        })
    } finally {
        isDeleting.value = false
    }
}

const toggleHistory = () => {
    isHistoryExpanded.value = !isHistoryExpanded.value
}

type RecipientDeliveryStatus = 'pending' | 'delivered' | 'failed'

const getLatestRecipientDeliveryStatus = (recipient: RecipientViewModel): RecipientDeliveryStatus => {
    return (recipient.latestDelivery?.status ?? 'pending') as RecipientDeliveryStatus
}

const recipientStatusLabel = (recipient: RecipientViewModel): string => {
    const status = getLatestRecipientDeliveryStatus(recipient)

    if (status === 'delivered') return trans('Delivered')
    if (status === 'failed') return trans('Delivery Failed')

    return trans('Pending Delivery')
}

const recipientStatusClass = (recipient: RecipientViewModel): string => {
    const status = getLatestRecipientDeliveryStatus(recipient)

    if (status === 'delivered') {
        return 'bg-green-50 text-green-700 border border-green-200 hover:bg-green-50 font-medium'
    }

    if (status === 'failed') {
        return 'bg-red-50 text-red-700 border border-red-200 hover:bg-red-50 font-medium'
    }

    return 'bg-yellow-50 text-yellow-700 border border-yellow-200 hover:bg-yellow-50 font-medium'
}

const recipientStatusUpdatedAt = (recipient: RecipientViewModel): string | null => {
    const status = getLatestRecipientDeliveryStatus(recipient)

    if (status === 'pending') {
        return null
    }

    const updatedAt = recipient.latestDelivery?.updatedAt

    if (!updatedAt) {
        return null
    }

    return dayjs(updatedAt).fromNow()
}
</script>

<template>
    <Head :title="trans('Custodianship') + ' - ' + custodianship.name"/>

    <AuthenticatedLayout>
        <div class="space-y-6">
            <!-- Breadcrumbs -->
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="flex items-center space-x-2 text-sm">
                    <li v-for="(item, index) in breadcrumbs" :key="item.name">
                        <div class="flex items-center">
                            <a
                                v-if="!item.current"
                                :href="item.href"
                                class="text-gray-500 hover:text-gray-700"
                            >
                                {{ item.name }}
                            </a>
                            <span v-else class="text-gray-900 font-medium">
                                {{ item.name }}
                            </span>
                            <svg
                                v-if="index < breadcrumbs.length - 1"
                                class="h-5 w-5 text-gray-400 mx-2"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    fill-rule="evenodd"
                                    d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                    clip-rule="evenodd"
                                />
                            </svg>
                        </div>
                    </li>
                </ol>
            </nav>

            <!-- Page Header -->
            <div class="flex items-start justify-between">
                <div class="space-y-2">
                    <div class="flex items-center space-x-3">
                        <h1 class="text-3xl font-bold text-gray-900">
                            {{ custodianship.name }}
                        </h1>
                        <StatusBadge
                            :status="statusDisplay"
                            :delivery-status="custodianship.deliveryStatus"
                        />
                    </div>
                    <p v-if="custodianship.resetCount" class="text-sm text-gray-500">
                        {{ trans('Reset :count times').replace(':count', custodianship.resetCount.toString()) }}
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <CustodianshipActions
                        :custodianship="custodianship"
                        :email-verified="user.emailVerified"
                    />

                    <Button v-if="!isExpired"
                        variant="outline"
                        @click="handleEdit"
                    >
                        <PencilIcon class="h-4 w-4 mr-2" />
                        {{ trans('Edit') }}
                    </Button>
                </div>
            </div>

            <!-- Delivery failed banner -->
            <div
                v-if="custodianship.deliveryStatus === 'failed' || custodianship.deliveryStatus === 'bounced'"
                class="bg-red-50 border border-red-200 rounded-lg p-4"
            >
                <p class="text-sm text-red-800 font-medium">
                    {{ trans('Email delivery failed. Please edit the custodianship to fix recipient email addresses.') }}
                </p>
            </div>

            <div class="space-y-6">
                <!-- Timer Section (only for active status) -->
                <TimerSection v-if="custodianship.status === 'active' && !isExpired" :custodianship="custodianship" />

                <!-- Message Content Section -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle>{{ trans('Message Content') }}</CardTitle>
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="isMessageVisible = !isMessageVisible"
                                class="text-xs"
                            >
                                <EyeIcon v-if="!isMessageVisible" class="h-4 w-4 mr-1" />
                                <EyeSlashIcon v-else class="h-4 w-4 mr-1" />
                                {{ isMessageVisible ? trans('Hide') : trans('Show') }}
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <MessageContentViewer v-if="isMessageVisible" :content="custodianship.messageContent ?? null" />
                            <div v-else class="text-center py-8 text-gray-400 italic">
                                {{ trans('Message content is hidden for privacy') }}
                            </div>
                        </div>
                        <div class="pt-4 border-t">
                            <p class="text-xs italic text-gray-400">
                                {{ trans('Message content is delivered as plain text to recipients when the timer expires.') }}
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recipients Section -->
                <Card>
                    <CardHeader>
                        <CardTitle>{{ trans('Recipients (:count)').replace(':count', (custodianship.recipients?.length || 0).toString()) }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="custodianship.recipients && custodianship.recipients.length > 0" class="space-y-3">
                            <div
                                v-for="recipient in custodianship.recipients"
                                :key="recipient.id"
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                            >
                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-medium text-gray-900">
                                        {{ recipient.email }}
                                    </span>
                                    <template v-if="isExpired">
                                        <Badge :class="recipientStatusClass(recipient)">
                                            {{ recipientStatusLabel(recipient) }}
                                        </Badge>
                                        <span
                                            v-if="recipientStatusUpdatedAt(recipient)"
                                            class="text-xs text-gray-500"
                                        >
                                            {{ trans('Updated :time').replace(':time', recipientStatusUpdatedAt(recipient) || '') }}
                                        </span>
                                    </template>
                                </div>
                                <span class="text-xs text-gray-500">
                                    {{ trans('Added :time').replace(':time', dayjs(recipient.createdAt).fromNow()) }}
                                </span>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-gray-400 italic">
                            {{ trans('(No recipients)') }}
                        </div>
                    </CardContent>
                </Card>

                <!-- Attachments Section -->
                <Card>
                    <CardHeader>
                        <CardTitle>{{ trans('Attachments (:count)').replace(':count', (custodianship.attachments?.length || 0).toString()) }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <AttachmentList
                            :attachments="custodianship.attachments || []"
                            :custodianship-uuid="custodianship.uuid"
                        />
                    </CardContent>
                </Card>
            </div>

            <!-- Reset History Section (Collapsible) -->
            <Card v-if="resetHistory.length > 0">
                <CardHeader>
                    <button
                        @click="toggleHistory"
                        class="flex items-center justify-between w-full text-left"
                    >
                        <CardTitle>{{ trans('Reset History (:count)').replace(':count', resetHistory.length.toString()) }}</CardTitle>
                        <ChevronDownIcon
                            v-if="!isHistoryExpanded"
                            class="h-5 w-5 text-gray-400"
                        />
                        <ChevronUpIcon
                            v-else
                            class="h-5 w-5 text-gray-400"
                        />
                    </button>
                </CardHeader>
                <CardContent v-if="isHistoryExpanded">
                    <ResetHistoryTable :reset-history="resetHistory" />
                </CardContent>
            </Card>

            <!-- Danger Zone -->
            <DangerZone
                :disabled="statusDisplay === 'pending'"
                @delete="isDeleteModalOpen = true"
            />
        </div>

        <!-- Delete Confirmation Modal -->
        <DeleteCustodianshipModal
            v-model:open="isDeleteModalOpen"
            :custodianship-name="custodianship.name"
            @confirm="handleDelete"
        />
    </AuthenticatedLayout>
</template>
