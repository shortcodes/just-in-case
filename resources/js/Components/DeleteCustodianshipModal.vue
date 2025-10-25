<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

interface DeleteCustodianshipModalProps {
    open: boolean
    custodianshipName: string
}

const props = defineProps<DeleteCustodianshipModalProps>()

const emit = defineEmits<{
    'update:open': [value: boolean]
    'confirm': []
}>()

const isChecked = ref(false)
const confirmationName = ref('')

const isDeleteEnabled = computed(() => {
    return isChecked.value && confirmationName.value.toLowerCase() === props.custodianshipName.toLowerCase()
})

watch(() => props.open, (newVal) => {
    if (!newVal) {
        isChecked.value = false
        confirmationName.value = ''
    }
})

const handleConfirm = () => {
    if (isDeleteEnabled.value) {
        emit('confirm')
    }
}

const handleOpenChange = (value: boolean) => {
    emit('update:open', value)
}
</script>

<template>
    <Dialog :open="open" @update:open="handleOpenChange">
        <DialogContent class="sm:max-w-md">
            <DialogHeader>
                <div class="flex items-center space-x-2 text-red-600 mb-2">
                    <ExclamationTriangleIcon class="h-6 w-6" />
                    <DialogTitle class="text-red-600">Delete Custodianship</DialogTitle>
                </div>
                <DialogDescription class="text-gray-600">
                    This action is permanent and cannot be undone. All data, attachments, and history will be permanently deleted.
                </DialogDescription>
            </DialogHeader>

            <div class="space-y-4 py-4">
                <div class="flex items-start space-x-2">
                    <Checkbox
                        id="confirm-checkbox"
                        :checked="isChecked"
                        @update:checked="isChecked = $event"
                    />
                    <Label
                        for="confirm-checkbox"
                        class="text-sm font-normal cursor-pointer leading-tight"
                    >
                        I understand this action is permanent
                    </Label>
                </div>

                <div class="space-y-2">
                    <Label for="confirmation-name" class="text-sm">
                        Type <span class="font-semibold">{{ custodianshipName }}</span> to confirm:
                    </Label>
                    <Input
                        id="confirmation-name"
                        v-model="confirmationName"
                        type="text"
                        placeholder="Enter custodianship name"
                        :class="[
                            'transition-colors',
                            confirmationName && isDeleteEnabled ? 'border-green-500 focus:border-green-500' : ''
                        ]"
                        @keydown.enter="handleConfirm"
                    />
                </div>
            </div>

            <DialogFooter>
                <Button
                    variant="outline"
                    @click="handleOpenChange(false)"
                >
                    Cancel
                </Button>
                <Button
                    variant="destructive"
                    :disabled="!isDeleteEnabled"
                    @click="handleConfirm"
                    class="bg-red-600 hover:bg-red-700"
                >
                    Delete Permanently
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
