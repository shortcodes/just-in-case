<script setup lang="ts">
import { computed } from 'vue'
import { Badge } from '@/components/ui/badge'
import type { StatusBadgeProps } from '@/types/components'

const props = defineProps<StatusBadgeProps>()

const badgeVariant = computed(() => {
    switch (props.status) {
        case 'active':
            return 'default'
        case 'draft':
            return 'secondary'
        case 'completed':
            return 'default'
        case 'pending':
            return 'destructive'
        default:
            return 'secondary'
    }
})

const badgeClass = computed(() => {
    switch (props.status) {
        case 'active':
            return 'bg-green-50 text-green-700 border border-green-200 hover:bg-green-50 font-medium'
        case 'draft':
            return 'bg-gray-50 text-gray-600 border border-gray-200 hover:bg-gray-50'
        case 'completed':
            return 'bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-50 font-medium'
        case 'pending':
            return 'bg-red-50 text-red-700 border border-red-200 hover:bg-red-50 font-semibold'
        default:
            if (props.deliveryStatus === 'failed' || props.deliveryStatus === 'bounced') {
                return 'bg-red-50 text-red-700 border border-red-300 hover:bg-red-50 font-medium'
            }
            return 'bg-gray-50 text-gray-600 border border-gray-200 hover:bg-gray-50'
    }
})

const statusLabel = computed(() => {
    if (props.status === 'pending') return 'Pending Delivery'
    if (props.status === 'completed') return 'Sent'
    if (props.status === 'draft') return 'Draft'
    if (props.status === 'active') return 'Active'

    if (props.deliveryStatus === 'failed' || props.deliveryStatus === 'bounced') {
        return 'Delivery Failed'
    }

    const status = String(props.status)
    return status.charAt(0).toUpperCase() + status.slice(1)
})
</script>

<template>
    <Badge
        :variant="badgeVariant"
        :class="badgeClass"
    >
        {{ statusLabel }}
    </Badge>
</template>
