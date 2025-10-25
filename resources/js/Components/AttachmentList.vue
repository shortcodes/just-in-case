<script setup lang="ts">
import AttachmentItem from './AttachmentItem.vue'
import type { AttachmentViewModel } from '@/types/models'

interface AttachmentListProps {
    attachments: AttachmentViewModel[]
    custodianshipUuid: string
}

const props = defineProps<AttachmentListProps>()
</script>

<template>
    <div v-if="attachments.length > 0" class="space-y-2">
        <AttachmentItem
            v-for="attachment in attachments"
            :key="attachment.id"
            :attachment="attachment"
            :custodianship-uuid="custodianshipUuid"
        />
    </div>
    <div v-else class="text-center py-8 text-gray-400 italic">
        (No attachments)
    </div>
</template>
