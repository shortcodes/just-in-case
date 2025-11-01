<script setup lang="ts">
import { ref } from 'vue'
import DangerZone from '@/Components/DangerZone.vue'
import DeleteAccountModal from '@/Components/DeleteAccountModal.vue'
import { useTrans } from '@/composables/useTrans'

const trans = useTrans()
const isDeleteModalOpen = ref(false)
</script>

<template>
    <div class="mt-10">
        <DangerZone
            @delete="isDeleteModalOpen = true"
        >
            <template #title>{{ trans('Delete Account') }}</template>
            <template #description>
                {{ trans('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
            </template>
            <template #button>{{ trans('Delete Account') }}</template>
        </DangerZone>

        <DeleteAccountModal
            v-model:open="isDeleteModalOpen"
        />
    </div>
</template>
