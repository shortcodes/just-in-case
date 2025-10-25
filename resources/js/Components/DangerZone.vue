<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

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
                Deleting this custodianship is permanent and cannot be undone.
            </CardDescription>
        </CardHeader>
        <CardContent>
            <Button
                variant="outline"
                class="border-red-300 text-red-700 hover:bg-red-100 hover:text-red-800"
                @click="emit('delete')"
            >
                <ExclamationTriangleIcon class="h-4 w-4 mr-2" />
                Delete Custodianship
            </Button>
        </CardContent>
    </Card>
</template>
