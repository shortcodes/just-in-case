<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import { ChevronDownIcon, PhotoIcon, XMarkIcon, DocumentIcon, PlusIcon } from '@heroicons/vue/24/outline'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Button } from '@/components/ui/button'
import StorageIndicator from '@/Components/StorageIndicator.vue'
import type { CreateCustodianshipPageProps, TempAttachment, CreateCustodianshipFormData } from '@/types/models'

const props = defineProps<CreateCustodianshipPageProps>()

const breadcrumbs = [
    { label: 'Custodianships', href: '/custodianships' },
    { label: 'Create New' },
]

const form = useForm<CreateCustodianshipFormData>({
    name: '',
    messageContent: null,
    intervalValue: 90,
    intervalUnit: 'days',
    recipients: [],
    attachments: [],
})

const uploadedAttachments = ref<TempAttachment[]>([])
const isDragging = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)

const totalAttachmentSize = computed(() => {
    return uploadedAttachments.value.reduce((sum, file) => sum + file.size, 0)
})

const showDraftInfoBanner = computed(() => {
    return true
})

const canAddRecipient = computed(() => form.recipients.length < 2)
const canAddFiles = computed(() => totalAttachmentSize.value < 10485760)

function handleSubmit() {
    form.transform((data) => ({
        ...data,
        attachments: uploadedAttachments.value.map(a => a.id),
    })).post(route('custodianships.store'), {
        preserveScroll: true,
    })
}

function handleCancel() {
    if (form.isDirty) {
        if (confirm('You have unsaved changes. Are you sure you want to leave?')) {
            router.visit(route('custodianships.index'))
        }
    } else {
        router.visit(route('custodianships.index'))
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
            alert(`Adding ${file.name} would exceed the 10MB limit.`)
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
    <Head title="Create New Custodianship" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <Breadcrumbs :items="breadcrumbs" />

            <Alert v-if="showDraftInfoBanner" class="border-blue-200 bg-blue-50">
                <AlertDescription class="text-blue-800">
                    Custodianship will be created as a draft. You can activate it later from the custodianship details page.
                </AlertDescription>
            </Alert>

            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Create New Custodianship</h1>
                <p class="mt-1 text-sm text-gray-600">
                    Set up a new message that will be sent to your recipients if you don't check in regularly.
                </p>
            </div>

            <form @submit.prevent="handleSubmit">
                <div>
                    <!-- Custodianship Details -->
                    <div class="grid grid-cols-1 gap-x-8 gap-y-10 border-t border-b border-gray-900/10 pt-10 pb-12 md:grid-cols-3">
                        <div>
                            <h2 class="text-base/7 font-semibold text-gray-900">Custodianship Details</h2>
                            <p class="mt-1 text-sm/6 text-gray-600">Basic information about your custodianship message.</p>
                        </div>

                        <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                            <div class="sm:col-span-4">
                                <Label for="name">Name</Label>
                                <div class="mt-2">
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        placeholder="Enter a name for this custodianship"
                                        :disabled="form.processing"
                                    />
                                </div>
                                <p v-if="form.errors.name" class="mt-3 text-sm/6 text-destructive">{{ form.errors.name }}</p>
                                <p v-else class="mt-3 text-sm/6 text-muted-foreground">
                                    A descriptive name for this custodianship (e.g., 'Family Emergency Info')
                                </p>
                            </div>

                            <div class="sm:col-span-3">
                                <Label for="intervalValue">Check-in Interval</Label>
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
                                    <Select v-model="form.intervalUnit" :disabled="form.processing">
                                        <SelectTrigger class="w-32">
                                            <SelectValue placeholder="Unit" />
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
                                    How often you need to check in to prevent the message from being sent
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Message Content -->
                    <div class="grid grid-cols-1 gap-x-8 gap-y-10 mt-10 border-b border-gray-900/10 pb-12 md:grid-cols-3">
                        <div>
                            <h2 class="text-base/7 font-semibold text-gray-900">Message Content</h2>
                            <p class="mt-1 text-sm/6 text-gray-600">The message and files that will be sent to your recipients.</p>
                        </div>

                        <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                            <div class="col-span-full">
                                <Label for="messageContent">Message</Label>
                                <div class="mt-2">
                                    <Textarea
                                        id="messageContent"
                                        :model-value="form.messageContent || ''"
                                        @update:model-value="form.messageContent = (typeof $event === 'string' ? $event : String($event)) || null"
                                        placeholder="Enter your message here..."
                                        rows="6"
                                        :disabled="form.processing"
                                    />
                                </div>
                                <p v-if="form.errors.messageContent" class="mt-3 text-sm/6 text-destructive">{{ form.errors.messageContent }}</p>
                                <p v-else class="mt-3 text-sm/6 text-muted-foreground">
                                    This content will be sent to recipients when the custodianship is triggered.
                                </p>
                            </div>

                            <div class="col-span-full">
                                <Label>Attachments</Label>
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
                                                    <span>Upload files</span>
                                                    <input
                                                        ref="fileInput"
                                                        type="file"
                                                        class="sr-only"
                                                        multiple
                                                        @change="handleFileSelect"
                                                        :disabled="!canAddFiles"
                                                    />
                                                </label>
                                                <p class="pl-1">or drag and drop</p>
                                            </div>
                                            <p class="text-xs/5 text-gray-600">Files up to 10MB total</p>
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
                            <h2 class="text-base/7 font-semibold text-gray-900">Recipients</h2>
                            <p class="mt-1 text-sm/6 text-gray-600">Who should receive your message (max 2 recipients in free plan).</p>
                        </div>

                        <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6 md:col-span-2">
                            <div class="col-span-full">
                                <Label>Email Addresses</Label>
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
                                            placeholder="recipient@example.com"
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
                                        Add recipient
                                        <span v-if="!canAddRecipient" class="ml-2 text-xs text-muted-foreground">
                                            ({{ form.recipients.length }}/2)
                                        </span>
                                    </Button>
                                </div>
                                <p v-if="form.errors.recipients" class="mt-3 text-sm/6 text-destructive">{{ form.errors.recipients }}</p>
                                <p v-else class="mt-3 text-sm/6 text-muted-foreground">
                                    Enter the email addresses of people who should receive your message
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
                        Cancel
                    </Button>
                    <Button
                        type="submit"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Saving...' : 'Save' }}
                    </Button>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
