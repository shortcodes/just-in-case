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
    // For completed status, show delivery status styling
    if (props.status === 'completed') {
        if (props.deliveryStatus === 'delivered') {
            return 'bg-green-50 text-green-700 border border-green-200 hover:bg-green-50 font-medium'
        }
        if (props.deliveryStatus === 'failed' || props.deliveryStatus === 'bounced') {
            return 'bg-red-50 text-red-700 border border-red-300 hover:bg-red-50 font-medium'
        }
        // Pending delivery (completed but no delivery status)
        return 'bg-yellow-50 text-yellow-700 border border-yellow-200 hover:bg-yellow-50 font-medium'
    }

    switch (props.status) {
        case 'active':
            return 'bg-green-50 text-green-700 border border-green-200 hover:bg-green-50 font-medium'
        case 'draft':
            return 'bg-gray-50 text-gray-600 border border-gray-200 hover:bg-gray-50'
        case 'pending':
            return 'bg-red-50 text-red-700 border border-red-200 hover:bg-red-50 font-semibold'
        default:
            return 'bg-gray-50 text-gray-600 border border-gray-200 hover:bg-gray-50'
    }
})

const statusLabel = computed(() => {
    // For completed status, show delivery status label
    if (props.status === 'completed') {
        if (props.deliveryStatus === 'delivered') return 'Delivered'
        if (props.deliveryStatus === 'failed' || props.deliveryStatus === 'bounced') return 'Delivery Failed'
        return 'Completed'
    }

    if (props.status === 'pending') return 'Pending Delivery'
    if (props.status === 'draft') return 'Draft'
    if (props.status === 'active') return 'Active'

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
