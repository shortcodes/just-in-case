<script setup lang="ts">
import { computed } from 'vue'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Button } from '@/components/ui/button'
import { ClockIcon, ExclamationCircleIcon } from '@heroicons/vue/24/outline'
import type { ExpiringCustodianshipsBannerProps } from '@/types/components'
import { useTrans } from '@/composables/useTrans'

const props = defineProps<ExpiringCustodianshipsBannerProps>()

const emit = defineEmits<{
    resetAll: []
}>()

const trans = useTrans()

const getMessage = computed(() => {
    const message = trans('common.expiring_custodianships_banner.message')
    const parts = message.split('|')
    const selectedMessage = props.expiringCount === 1 ? parts[0] : (parts[1] || parts[0])
    return selectedMessage.replace(':count', props.expiringCount.toString())
})

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
                        {{ trans('common.expiring_custodianships_banner.title') }}
                    </h3>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        {{ expiringCount }}
                    </span>
                </div>
                <div class="flex items-center gap-2 text-sm text-red-800">
                    <ClockIcon class="h-4 w-4" />
                    <span>
                        {{ getMessage }}
                    </span>
                </div>
            </div>
            <div class="flex-shrink-0">
                <Button
                    size="sm"
                    class="bg-red-600 text-white hover:bg-red-700"
                    @click="handleResetAll"
                >
                    {{ trans('common.expiring_custodianships_banner.reset_all') }}
                </Button>
            </div>
        </div>
    </div>
</template>
