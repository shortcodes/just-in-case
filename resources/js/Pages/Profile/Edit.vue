<script setup lang="ts">
import { computed } from 'vue'
import { Head } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import Breadcrumbs from '@/Components/Breadcrumbs.vue'
import DeleteUserForm from './Partials/DeleteUserForm.vue'
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue'
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue'
import { useTrans } from '@/composables/useTrans'

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
})

const trans = useTrans()

const breadcrumbs = computed(() => [
    { label: trans('Profile') }
])
</script>

<template>
    <Head :title="trans('Profile')" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <Breadcrumbs :items="breadcrumbs" />

            <div>
                <h1 class="text-2xl font-semibold text-gray-900">{{ trans('Profile') }}</h1>
                <p class="mt-1 text-sm text-gray-600">
                    {{ trans('Manage your account settings and preferences.') }}
                </p>
            </div>

            <div>
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                />

                <UpdatePasswordForm />

                <DeleteUserForm />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
