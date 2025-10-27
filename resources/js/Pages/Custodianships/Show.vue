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
import { PencilIcon, ArrowPathIcon, ChevronDownIcon, ChevronUpIcon, EyeIcon, EyeSlashIcon } from '@heroicons/vue/24/outline'
import type { ShowCustodianshipPageProps } from '@/types/models'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'

dayjs.extend(relativeTime)

const props = defineProps<ShowCustodianshipPageProps>()

const custodianship = ref(props.custodianship)
const resetHistory = ref(props.resetHistory || [])

const isResetting = ref(false)
const isDeleting = ref(false)
const isDeleteModalOpen = ref(false)
const isHistoryExpanded = ref(false)
const isMessageVisible = ref(false)

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
    { name: 'Custodianships', href: route('custodianships.index') },
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
            route('custodianships.reset', custodianship.value.uuid),
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

            <!-- Delivery failed banner -->
            <div
                v-if="custodianship.deliveryStatus === 'failed' || custodianship.deliveryStatus === 'bounced'"
                class="bg-red-50 border border-red-200 rounded-lg p-4"
            >
                <p class="text-sm text-red-800 font-medium">
                    Email delivery failed. Please edit the custodianship to fix recipient email addresses.
                </p>
            </div>

            <div class="space-y-6">
                <!-- Timer Section (only for active status) -->
                <TimerSection v-if="custodianship.status === 'active'" :custodianship="custodianship" />

                <!-- Message Content Section -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <CardTitle>Message Content</CardTitle>
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="isMessageVisible = !isMessageVisible"
                                class="text-xs"
                            >
                                <EyeIcon v-if="!isMessageVisible" class="h-4 w-4 mr-1" />
                                <EyeSlashIcon v-else class="h-4 w-4 mr-1" />
                                {{ isMessageVisible ? 'Hide' : 'Show' }}
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <MessageContentViewer v-if="isMessageVisible" :content="custodianship.messageContent" />
                            <div v-else class="text-center py-8 text-gray-400 italic">
                                Message content is hidden for privacy
                            </div>
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

                <!-- Recipients Section -->
                <Card>
                    <CardHeader>
                        <CardTitle>Recipients ({{ custodianship.recipients?.length || 0 }})</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="custodianship.recipients && custodianship.recipients.length > 0" class="space-y-3">
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
                        <CardTitle>Attachments ({{ custodianship.attachments?.length || 0 }})</CardTitle>
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
