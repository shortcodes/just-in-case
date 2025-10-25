<script setup lang="ts">
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Button } from '@/components/ui/button'
import { ClockIcon, ExclamationCircleIcon } from '@heroicons/vue/24/outline'
import type { ExpiringCustodianshipsBannerProps } from '@/types/components'

defineProps<ExpiringCustodianshipsBannerProps>()

const emit = defineEmits<{
    resetAll: []
}>()

const handleResetAll = () => {
    emit('resetAll')
}
</script>

<template>
    <div class="bg-gradient-to-r from-red-50 to-rose-50 border-l-4 border-l-red-500 rounded-lg p-4 mb-6 shadow-sm">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
                <div class="flex items-center justify-center h-10 w-10 rounded-full bg-red-100">
                    <ExclamationCircleIcon class="h-6 w-6 text-red-600" />
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <h3 class="text-sm font-semibold text-red-900">
                        Urgent: Custodianships Expiring Soon
                    </h3>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        {{ expiringCount }}
                    </span>
                </div>
                <div class="flex items-center gap-2 text-sm text-red-800">
                    <ClockIcon class="h-4 w-4" />
                    <span>
                        {{ expiringCount }} custodianship{{ expiringCount > 1 ? 's' : '' }} will trigger in less than 7 days. Reset timers to prevent automatic message delivery.
                    </span>
                </div>
            </div>
            <div class="flex-shrink-0">
                <Button
                    size="sm"
                    class="bg-red-600 text-white hover:bg-red-700"
                    @click="handleResetAll"
                >
                    Reset All
                </Button>
            </div>
        </div>
    </div>
</template>
