<script setup lang="ts">
import { computed } from 'vue'

interface MessageContentViewerProps {
    content: string | null
}

const props = defineProps<MessageContentViewerProps>()

const hasContent = computed(() => props.content && props.content.trim().length > 0)
</script>

<template>
    <div class="prose prose-sm max-w-none">
        <div v-if="hasContent" class="text-gray-700 whitespace-pre-wrap break-words">
            {{ content }}
        </div>
        <div v-else class="text-gray-400 italic">
            (No message content)
        </div>
    </div>
</template>

<style scoped>
.prose {
    @apply text-gray-700;
}
</style>
