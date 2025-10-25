<script setup lang="ts">
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import StatusBadge from '@/Components/StatusBadge.vue'
import TimerSection from '@/Components/TimerSection.vue'
import MessageContentViewer from '@/Components/MessageContentViewer.vue'
import AttachmentList from '@/Components/AttachmentList.vue'
import ResetHistoryTable from '@/Components/ResetHistoryTable.vue'
import DeleteCustodianshipModal from '@/Components/DeleteCustodianshipModal.vue'
import DangerZone from '@/Components/DangerZone.vue'
import ConfirmableButton from '@/Components/ConfirmableButton.vue'
import { PencilIcon, ArrowPathIcon, ChevronDownIcon, ChevronUpIcon } from '@heroicons/vue/24/outline'
import type { ShowCustodianshipPageProps } from '@/types/models'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
import { mockShowPageProps } from '@/data/mockCustodianships'

dayjs.extend(relativeTime)

const props = defineProps<ShowCustodianshipPageProps>()

// Use mock data for development
const custodianship = ref(mockShowPageProps.custodianship)
const resetHistory = ref(mockShowPageProps.resetHistory || [])

const isResetting = ref(false)
const isDeleting = ref(false)
const isDeleteModalOpen = ref(false)
const isHistoryExpanded = ref(false)

const canReset = computed(() => {
    if (custodianship.value.status !== 'active') return false
    if (!custodianship.value.nextTriggerAt) return false
    return !dayjs(custodianship.value.nextTriggerAt).isBefore(dayjs())
})

const isExpired = computed(() => {
    if (!custodianship.value.nextTriggerAt) return false
    return dayjs(custodianship.value.nextTriggerAt).isBefore(dayjs())
})

const statusDisplay = computed(() => {
    if (isExpired.value) return 'pending'
    return custodianship.value.status
})

const breadcrumbs = computed(() => [
    { name: 'Custodianships', href: '/custodianships' },
    { name: custodianship.value.name, href: '#', current: true }
])

const handleReset = async () => {
    if (!canReset.value) return

    isResetting.value = true

    const oldLastResetAt = custodianship.value.lastResetAt
    const oldNextTriggerAt = custodianship.value.nextTriggerAt

    custodianship.value.lastResetAt = dayjs().toISOString()
    custodianship.value.nextTriggerAt = dayjs()
        .add(custodianship.value.intervalDays, 'day')
        .toISOString()

    try {
        await router.post(
            `/custodianships/${custodianship.value.uuid}/reset`,
            {},
            {
                preserveScroll: true,
                onError: () => {
                    custodianship.value.lastResetAt = oldLastResetAt
                    custodianship.value.nextTriggerAt = oldNextTriggerAt
                }
            }
        )
    } finally {
        isResetting.value = false
    }
}

const handleEdit = () => {
    router.visit(`/custodianships/${custodianship.value.uuid}/edit`)
}

const handleDelete = async () => {
    isDeleting.value = true

    try {
        await router.delete(`/custodianships/${custodianship.value.uuid}`, {
            onSuccess: () => {
                router.visit('/custodianships')
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
</script>

<template>
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
                        Reset {{ custodianship.resetCount }} times
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <Button
                        variant="outline"
                        @click="handleEdit"
                    >
                        <PencilIcon class="h-4 w-4 mr-2" />
                        Edit
                    </Button>
                    <ConfirmableButton
                        v-if="custodianship.status === 'active'"
                        label="Reset Timer"
                        confirm-label="Confirm Reset"
                        :disabled="!canReset || isResetting"
                        :tooltip-disabled="isExpired ? 'Cannot reset - message will be sent shortly' : ''"
                        @confirm="handleReset"
                        class="bg-green-600 hover:bg-green-700 text-white"
                    >
                        <template #icon>
                            <ArrowPathIcon :class="['h-4 w-4 mr-2', isResetting ? 'animate-spin' : '']" />
                        </template>
                    </ConfirmableButton>
                </div>
            </div>

            <!-- Timer expired banner -->
            <div
                v-if="isExpired && custodianship.status === 'active'"
                class="bg-amber-50 border border-amber-200 rounded-lg p-4"
            >
                <p class="text-sm text-amber-800 font-medium">
                    Your timer has expired. The message will be sent shortly unless you reset it.
                </p>
            </div>

            <!-- Draft status banner -->
            <div
                v-if="custodianship.status === 'draft'"
                class="bg-blue-50 border border-blue-200 rounded-lg p-4"
            >
                <div class="flex items-center justify-between">
                    <p class="text-sm text-blue-800">
                        This custodianship is a draft. Verify your email to activate it.
                    </p>
                </div>
            </div>

            <!-- Delivery failed banner -->
            <div
                v-if="custodianship.deliveryStatus === 'failed' || custodianship.deliveryStatus === 'bounced'"
                class="bg-red-50 border border-red-200 rounded-lg p-4"
            >
                <p class="text-sm text-red-800 font-medium">
                    Email delivery failed. Please edit the custodianship to fix recipient email addresses.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Timer Section -->
                    <TimerSection :custodianship="custodianship" />

                    <!-- Details Section -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Message Details</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <h3 class="text-sm font-medium text-gray-700 mb-2">Message Content</h3>
                                <MessageContentViewer :content="custodianship.messageContent" />
                            </div>
                            <div class="pt-4 border-t">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Check-in Interval</span>
                                    <span class="font-medium text-gray-900">
                                        {{ custodianship.intervalDays }} days
                                    </span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Recipients Section -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Recipients ({{ custodianship.recipients.length }})</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div v-if="custodianship.recipients.length > 0" class="space-y-3">
                                <div
                                    v-for="recipient in custodianship.recipients"
                                    :key="recipient.id"
                                    class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                                >
                                    <span class="text-sm text-gray-900">{{ recipient.email }}</span>
                                    <span class="text-xs text-gray-500">
                                        Added {{ dayjs(recipient.createdAt).fromNow() }}
                                    </span>
                                </div>
                            </div>
                            <div v-else class="text-center py-8 text-gray-400 italic">
                                (No recipients)
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Attachments Section -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Attachments ({{ custodianship.attachments.length }})</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <AttachmentList
                                :attachments="custodianship.attachments"
                                :custodianship-uuid="custodianship.uuid"
                            />
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Reset History Section (Collapsible) -->
            <Card v-if="resetHistory.length > 0">
                <CardHeader>
                    <button
                        @click="toggleHistory"
                        class="flex items-center justify-between w-full text-left"
                    >
                        <CardTitle>Reset History ({{ resetHistory.length }})</CardTitle>
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
            <DangerZone @delete="isDeleteModalOpen = true" />
        </div>

        <!-- Delete Confirmation Modal -->
        <DeleteCustodianshipModal
            v-model:open="isDeleteModalOpen"
            :custodianship-name="custodianship.name"
            @confirm="handleDelete"
        />
    </AuthenticatedLayout>
</template>
