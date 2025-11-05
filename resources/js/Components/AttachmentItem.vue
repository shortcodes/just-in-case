<script setup lang="ts">
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import type { AttachmentViewModel } from '@/types/models'
import {
    DocumentTextIcon,
    DocumentIcon,
    PhotoIcon,
    FilmIcon,
    MusicalNoteIcon,
    ArchiveBoxIcon,
    ArrowDownTrayIcon,
} from '@heroicons/vue/24/outline'

interface AttachmentItemProps {
    attachment: AttachmentViewModel
    custodianshipUuid: string
}

const props = defineProps<AttachmentItemProps>()

const fileIcon = computed(() => {
    const mimeType = props.attachment.mimeType.toLowerCase()

    if (mimeType.includes('pdf')) return DocumentTextIcon
    if (mimeType.includes('word') || mimeType.includes('document')) return DocumentTextIcon
    if (mimeType.includes('image')) return PhotoIcon
    if (mimeType.includes('video')) return FilmIcon
    if (mimeType.includes('audio')) return MusicalNoteIcon
    if (mimeType.includes('zip') || mimeType.includes('rar') || mimeType.includes('7z')) return ArchiveBoxIcon

    return DocumentIcon
})

const formattedSize = computed(() => {
    const bytes = props.attachment.size
    if (bytes < 1024) return `${bytes} B`
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`
})

const downloadUrl = computed(() => {
    return route('custodianships.attachments.download', {
        custodianship: props.custodianshipUuid,
        attachment: props.attachment.id,
    })
})

const handleDownload = () => {
    window.open(downloadUrl.value, '_blank')
}
</script>

<template>
    <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
        <div class="flex items-center space-x-3 min-w-0">
            <component
                :is="fileIcon"
                class="h-8 w-8 text-gray-400 flex-shrink-0"
            />
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-gray-900 truncate">
                    {{ attachment.name }}
                </p>
                <p class="text-xs text-gray-500">
                    {{ formattedSize }}
                </p>
            </div>
        </div>
        <Button
            variant="outline"
            size="sm"
            @click="handleDownload"
            class="flex-shrink-0 ml-3"
        >
            <ArrowDownTrayIcon class="h-4 w-4 mr-1" />
            Download
        </Button>
    </div>
</template>
