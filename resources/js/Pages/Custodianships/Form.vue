<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { ChevronDownIcon, PhotoIcon, XMarkIcon, DocumentIcon, PlusIcon } from '@heroicons/vue/24/outline'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Button } from '@/components/ui/button'
import StorageIndicator from '@/Components/StorageIndicator.vue'
import { useTrans } from '@/composables/useTrans'
import type { CreateCustodianshipPageProps, EditCustodianshipPageProps, TempAttachment, CreateCustodianshipFormData } from '@/types/models'

type CustodianshipFormProps = CreateCustodianshipPageProps | EditCustodianshipPageProps

const props = defineProps<CustodianshipFormProps>()
const trans = useTrans()

const existingCustodianship = computed(() => (props as Partial<EditCustodianshipPageProps>).custodianship ?? null)
const isEditMode = computed(() => existingCustodianship.value !== null)

const parseIntervalDays = (days: number): { value: number, unit: string } => {
    const totalMinutes = days * 24 * 60

    // If less than 1 hour, use minutes
    if (totalMinutes < 60) {
        return { value: Math.round(totalMinutes), unit: 'minutes' }
    }

    // If less than 1 day, use hours
    if (days < 1) {
        const hours = totalMinutes / 60
        return { value: Math.round(hours), unit: 'hours' }
    }

    // Otherwise use days
    return { value: Math.round(days), unit: 'days' }
}

const getInitialFormData = (): CreateCustodianshipFormData => {
    if (isEditMode.value && existingCustodianship.value) {
        const custodianship = existingCustodianship.value
        const interval = parseIntervalDays(custodianship.intervalDays || 90)

        const recipients = Array.isArray(custodianship.recipients)
            ? custodianship.recipients.map(r => r.email)
            : []

        return {
            name: custodianship.name,
            messageContent: custodianship.messageContent || null,
            intervalValue: interval.value,
            intervalUnit: interval.unit,
            recipients,
            attachments: [],
        }
    }
    return {
        name: '',
        messageContent: null,
        intervalValue: 90,
        intervalUnit: 'days',
        recipients: [],
        attachments: [],
    }
}

const breadcrumbs = computed(() => [
    { label: trans('Custodianships'), href: route('custodianships.index') },
    ...(isEditMode.value && existingCustodianship.value
        ? [
            { label: existingCustodianship.value.name, href: route('custodianships.show', existingCustodianship.value.uuid) },
            { label: trans('Edit') }
        ]
        : [{ label: trans('Create New') }]
    ),
])

const form = useForm<CreateCustodianshipFormData>(getInitialFormData())

const uploadedAttachments = ref<TempAttachment[]>([])
const isDragging = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)
const isAwaitingResetDecision = ref(false)
const shouldResetTimer = ref(false)

const totalAttachmentSize = computed(() => {
    return uploadedAttachments.value.reduce((sum, file) => sum + file.size, 0)
})

const canAddRecipient = computed(() => form.recipients.length < 2)
const canAddFiles = computed(() => totalAttachmentSize.value < 10485760)

const isActiveStatus = computed(() => existingCustodianship.value?.status === 'active')
const canResetOnSave = computed(() => isEditMode.value && isActiveStatus.value)

const pageTitle = computed(() => isEditMode.value ? trans('Edit Custodianship') : trans('Create New Custodianship'))
const pageDescription = computed(() =>
    isEditMode.value
        ? trans('Update your custodianship message and settings.')
        : trans('Set up a new message that will be sent to your recipients if you don\'t check in regularly.')
)

function handleSaveClick() {
    if (canResetOnSave.value) {
        isAwaitingResetDecision.value = true
    } else {
        performSave(false)
    }
}

function handleResetDecision(reset: boolean) {
    shouldResetTimer.value = reset
    isAwaitingResetDecision.value = false
    performSave(reset)
}

function performSave(resetTimer: boolean) {
    const custodianshipId = existingCustodianship.value?.uuid ?? null

    form.transform((data) => ({
        ...data,
        attachments: uploadedAttachments.value.map(a => a.id),
    }))

    const onSuccess = () => {
        if (resetTimer && custodianshipId) {
            router.post(
                route('custodianships.reset', custodianshipId),
                {},
                {
                    preserveState: false,
                }
            )
        }
    }

    if (isEditMode.value && custodianshipId) {
        form.patch(route('custodianships.update', custodianshipId), {
            preserveScroll: true,
            onSuccess,
        })
    } else {
        form.post(route('custodianships.store'), {
            preserveScroll: true,
        })
    }
}

function handleCancel() {
    const returnRoute = isEditMode.value && existingCustodianship.value
        ? route('custodianships.show', existingCustodianship.value.uuid)
        : route('custodianships.index')

    if (form.isDirty) {
        if (confirm(trans('You have unsaved changes. Are you sure you want to leave?'))) {
            router.visit(returnRoute)
        }
    } else {
        router.visit(returnRoute)
    }
}

function updateRecipient(index: number, value: string) {
    form.recipients[index] = value
}

function removeRecipient(index: number) {
    form.recipients.splice(index, 1)
}

function addRecipient() {
    if (canAddRecipient.value) {
        form.recipients.push('')
    }
}

function handleDragOver(e: DragEvent) {
    e.preventDefault()
    isDragging.value = true
}

function handleDragLeave() {
    isDragging.value = false
}

function handleDrop(e: DragEvent) {
    e.preventDefault()
    isDragging.value = false
    if (e.dataTransfer?.files) {
        handleFiles(Array.from(e.dataTransfer.files))
    }
}

function handleFileSelect(e: Event) {
    const target = e.target as HTMLInputElement
    if (target.files) {
        handleFiles(Array.from(target.files))
    }
}

function handleFiles(files: File[]) {
    const newAttachments: TempAttachment[] = []
    for (const file of files) {
        const potentialTotalSize = totalAttachmentSize.value + file.size + newAttachments.reduce((sum, a) => sum + a.size, 0)
        if (potentialTotalSize > 10485760) {
            alert(trans('Adding :filename would exceed the 10MB limit.').replace(':filename', file.name))
            break
        }
        newAttachments.push({
            id: crypto.randomUUID(),
            name: file.name,
            size: file.size,
            mimeType: file.type || 'application/octet-stream',
            tempPath: '',
            uploadProgress: 100
        })
    }
    if (newAttachments.length > 0) {
        uploadedAttachments.value.push(...newAttachments)
    }
}

function removeFile(id: string) {
    uploadedAttachments.value = uploadedAttachments.value.filter(f => f.id !== id)
}

function formatBytes(bytes: number): string {
    if (bytes === 0) return '0 Bytes'
    const k = 1024
    const sizes = ['Bytes', 'KB', 'MB']
    const i = Math.floor(Math.log(bytes) / Math.log(k))
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

function openFileDialog() {
    fileInput.value?.click()
}
</script>

<template>
    <Head :title="pageTitle" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <Breadcrumbs :items="breadcrumbs" />

            <div>
                <h1 class="text-2xl font-semibold text-gray-900">{{ pageTitle }}</h1>
                <p class="mt-1 text-sm text-gray-600">
                    {{ pageDescription }}
                </p>
            </div>

            <form @submit.prevent="handleSaveClick">
                <div>
                    <!-- Custodianship Details -->
                    <div class="grid grid-cols-1 gap-x-8 gap-y-10 border-t border-b border-gray-900/10 pt-10 pb-12 md:grid-cols-3">
                        <div>
                            <h2 class="text-base/7 font-semibold text-gray-900">{{ trans('Custodianship Details') }}</h2>
                            <p class="mt-1 text-sm/6 text-gray-600">{{ trans('Basic information about your custodianship message.') }}</p>
                        </div>

                        <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                            <div class="sm:col-span-4">
                                <Label for="name">{{ trans('Name') }}</Label>
                                <div class="mt-2">
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        :placeholder="trans('Enter a name for this custodianship')"
                                        :disabled="form.processing"
                                    />
                                </div>
                                <p v-if="form.errors.name" class="mt-3 text-sm/6 text-destructive">{{ form.errors.name }}</p>
                                <p v-else class="mt-3 text-sm/6 text-muted-foreground">
                                    {{ trans('A descriptive name for this custodianship (e.g., \'Family Emergency Info\')') }}
                                </p>
                            </div>

                            <div class="sm:col-span-3">
                                <Label for="intervalValue">{{ trans('Check-in Interval') }}</Label>
                                <div class="mt-2 flex gap-2">
                                    <Input
                                        id="intervalValue"
                                        v-model.number="form.intervalValue"
                                        type="number"
                                        min="1"
                                        placeholder="90"
                                        :disabled="form.processing"
                                        class="flex-1"
                                    />
                                    <Select
                                        :model-value="form.intervalUnit"
                                        @update:model-value="form.intervalUnit = String($event)"
                                        :disabled="form.processing"
                                    >
                                        <SelectTrigger class="w-32">
                                            <SelectValue :placeholder="trans('Unit')" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="unit in props.intervalUnits"
                                                :key="unit.value"
                                                :value="unit.value"
                                            >
                                                {{ unit.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <p v-if="form.errors.intervalValue || form.errors.intervalUnit" class="mt-3 text-sm/6 text-destructive">
                                    {{ form.errors.intervalValue || form.errors.intervalUnit }}
                                </p>
                                <p v-else class="mt-3 text-sm/6 text-muted-foreground">
                                    {{ trans('How often you need to check in to prevent the message from being sent') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Message Content -->
                    <div class="grid grid-cols-1 gap-x-8 gap-y-10 mt-10 border-b border-gray-900/10 pb-12 md:grid-cols-3">
                        <div>
                            <h2 class="text-base/7 font-semibold text-gray-900">{{ trans('Message Content') }}</h2>
                            <p class="mt-1 text-sm/6 text-gray-600">{{ trans('The message and files that will be sent to your recipients.') }}</p>
                        </div>

                        <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                            <div class="col-span-full">
                                <Label for="messageContent">{{ trans('Message') }}</Label>
                                <div class="mt-2">
                                    <Textarea
                                        id="messageContent"
                                        :model-value="form.messageContent || ''"
                                        @update:model-value="form.messageContent = (typeof $event === 'string' ? $event : String($event)) || null"
                                        :placeholder="trans('Enter your message here...')"
                                        rows="6"
                                        :disabled="form.processing"
                                    />
                                </div>
                                <p v-if="form.errors.messageContent" class="mt-3 text-sm/6 text-destructive">{{ form.errors.messageContent }}</p>
                                <p v-else class="mt-3 text-sm/6 text-muted-foreground">
                                    {{ trans('This content will be sent to recipients when the custodianship is triggered.') }}
                                </p>
                            </div>

                            <div class="col-span-full">
                                <Label>{{ trans('Attachments') }}</Label>
                                <div class="mt-2">
                                    <div
                                        @dragover="handleDragOver"
                                        @dragleave="handleDragLeave"
                                        @drop="handleDrop"
                                        @click="canAddFiles ? openFileDialog() : null"
                                        :class="[
                                            'flex justify-center rounded-lg border border-dashed border-gray-900/25 px-6 py-10 transition-colors cursor-pointer',
                                            isDragging ? 'border-primary bg-primary/10' : '',
                                            !canAddFiles ? 'opacity-50 cursor-not-allowed' : ''
                                        ]"
                                    >
                                        <div class="text-center">
                                            <PhotoIcon class="mx-auto size-12 text-gray-300" aria-hidden="true" />
                                            <div class="mt-4 flex text-sm/6 text-gray-600">
                                                <label class="relative cursor-pointer rounded-md bg-white font-semibold text-indigo-600 focus-within:outline focus-within:outline-2 focus-within:outline-offset-2 focus-within:outline-indigo-600 hover:text-indigo-500">
                                                    <span>{{ trans('Upload files') }}</span>
                                                    <input
                                                        ref="fileInput"
                                                        type="file"
                                                        class="sr-only"
                                                        multiple
                                                        @change="handleFileSelect"
                                                        :disabled="!canAddFiles"
                                                    />
                                                </label>
                                                <p class="pl-1">{{ trans('or drag and drop') }}</p>
                                            </div>
                                            <p class="text-xs/5 text-gray-600">{{ trans('Files up to 10MB total') }}</p>
                                        </div>
                                    </div>

                                    <div v-if="uploadedAttachments.length > 0" class="mt-4 space-y-2">
                                        <div
                                            v-for="file in uploadedAttachments"
                                            :key="file.id"
                                            class="flex items-center justify-between rounded-lg border p-3"
                                        >
                                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                                <DocumentIcon class="h-5 w-5 flex-shrink-0 text-muted-foreground" />
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm font-medium truncate">{{ file.name }}</p>
                                                    <p class="text-xs text-muted-foreground">{{ formatBytes(file.size) }}</p>
                                                </div>
                                            </div>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                @click="removeFile(file.id)"
                                                class="flex-shrink-0"
                                            >
                                                <XMarkIcon class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <StorageIndicator :used-size="totalAttachmentSize" :max-size="10485760" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recipients -->
                    <div class="grid grid-cols-1 gap-x-8 gap-y-10 mt-10 border-b border-gray-900/10 pb-12 md:grid-cols-3">
                        <div>
                            <h2 class="text-base/7 font-semibold text-gray-900">{{ trans('Recipients') }}</h2>
                            <p class="mt-1 text-sm/6 text-gray-600">{{ trans('Who should receive your message (max 2 recipients in free plan).') }}</p>
                        </div>

                        <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                            <div class="col-span-full">
                                <Label>{{ trans('Email Addresses') }}</Label>
                                <div class="mt-2 space-y-3">
                                    <div
                                        v-for="(email, index) in form.recipients"
                                        :key="index"
                                        class="flex gap-2 items-start"
                                    >
                                        <Input
                                            :model-value="email"
                                            @update:model-value="updateRecipient(index, String($event))"
                                            type="email"
                                            :placeholder="trans('recipient@example.com')"
                                            :disabled="form.processing"
                                            class="flex-1"
                                        />
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="icon"
                                            @click="removeRecipient(index)"
                                        >
                                            <XMarkIcon class="h-4 w-4" />
                                        </Button>
                                    </div>

                                    <Button
                                        type="button"
                                        variant="outline"
                                        @click="addRecipient"
                                        :disabled="!canAddRecipient"
                                        class="w-full"
                                    >
                                        <PlusIcon class="h-4 w-4 mr-2" />
                                        {{ trans('Add recipient') }}
                                        <span v-if="!canAddRecipient" class="ml-2 text-xs text-muted-foreground">
                                            ({{ form.recipients.length }}/2)
                                        </span>
                                    </Button>
                                </div>
                                <p v-if="form.errors.recipients" class="mt-3 text-sm/6 text-destructive">{{ form.errors.recipients }}</p>
                                <p v-else class="mt-3 text-sm/6 text-muted-foreground">
                                    {{ trans('Enter the email addresses of people who should receive your message') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-x-6">
                    <Button
                        type="button"
                        variant="ghost"
                        @click="handleCancel"
                        :disabled="form.processing"
                    >
                        {{ trans('Cancel') }}
                    </Button>

                    <template v-if="isAwaitingResetDecision">
                        <Button
                            type="button"
                            variant="default"
                            class="bg-green-600 hover:bg-green-700 text-white"
                            :disabled="form.processing"
                            @click="handleResetDecision(true)"
                        >
                            {{ trans('Save & Reset Timer') }}
                        </Button>
                        <Button
                            type="button"
                            variant="outline"
                            :disabled="form.processing"
                            @click="handleResetDecision(false)"
                        >
                            {{ trans('Save Only') }}
                        </Button>
                    </template>

                    <Button
                        v-else
                        type="submit"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? trans('Saving...') : trans('Save') }}
                    </Button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
