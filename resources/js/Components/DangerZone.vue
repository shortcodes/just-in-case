<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'

const props = defineProps<{
    disabled?: boolean
}>()

const emit = defineEmits<{
    'delete': []
}>()
</script>

<template>
    <Card class="border-red-200 bg-red-50/30">
        <CardHeader>
            <div class="flex items-center space-x-2">
                <ExclamationTriangleIcon class="h-5 w-5 text-red-600" />
                <CardTitle class="text-red-900">Danger Zone</CardTitle>
            </div>
            <CardDescription class="text-red-700">
                <template v-if="props.disabled">
                    Deletion is temporarily locked while deliveries are pending.
                </template>
                <template v-else>
                    Deleting this custodianship is permanent and cannot be undone.
                </template>
            </CardDescription>
        </CardHeader>
        <CardContent>
            <TooltipProvider>
                <Tooltip :open="props.disabled ? undefined : false">
                    <TooltipTrigger as-child>
                        <span>
                            <Button
                                variant="outline"
                                class="border-red-300 text-red-700 hover:bg-red-100 hover:text-red-800"
                                :disabled="props.disabled"
                                :class="props.disabled ? 'opacity-60 cursor-not-allowed pointer-events-none' : ''"
                                @click="emit('delete')"
                            >
                                <ExclamationTriangleIcon class="h-4 w-4 mr-2" />
                                Delete Custodianship
                            </Button>
                        </span>
                    </TooltipTrigger>
                    <TooltipContent v-if="props.disabled">
                        <p>Deliveries are still pending. Delete becomes available after completion.</p>
                    </TooltipContent>
                </Tooltip>
            </TooltipProvider>
        </CardContent>
    </Card>
</template>
