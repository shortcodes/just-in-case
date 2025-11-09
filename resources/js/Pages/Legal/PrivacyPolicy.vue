<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import WelcomeLayout from '@/Layouts/WelcomeLayout.vue'
import { useTrans } from '@/composables/useTrans'

defineProps<{
    canLogin?: boolean
    canRegister?: boolean
}>()

const trans = useTrans()
const page = usePage()
const lastUpdated = computed(() => page.props.legal_documents_last_updated as string)
</script>

<template>
    <Head :title="trans('legal.privacy_policy.title')" />

    <WelcomeLayout :can-login="canLogin" :can-register="canRegister">
        <div class="mx-auto max-w-4xl px-6 py-16 sm:py-24 lg:px-8">
            <h1 class="text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl mb-4">
                {{ trans('legal.privacy_policy.title') }}
            </h1>

            <div class="prose prose-slate max-w-none">
                <p class="text-sm text-muted-foreground mb-6">
                    {{ trans('legal.privacy_policy.last_updated') }}: {{ lastUpdated }}
                </p>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.privacy_policy.data_controller.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.privacy_policy.data_controller.content') }}</p>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.privacy_policy.data_collected.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.privacy_policy.data_collected.intro') }}</p>
                    <ul class="list-disc list-inside text-sm text-muted-foreground space-y-1 ml-4">
                        <li v-for="(item, index) in trans('legal.privacy_policy.data_collected.items')" :key="index">{{ item }}</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.privacy_policy.legal_basis.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.privacy_policy.legal_basis.intro') }}</p>
                    <ul class="list-disc list-inside text-sm text-muted-foreground space-y-1 ml-4">
                        <li v-for="(item, index) in trans('legal.privacy_policy.legal_basis.items')" :key="index">{{ item }}</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.privacy_policy.data_usage.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.privacy_policy.data_usage.intro') }}</p>
                    <ul class="list-disc list-inside text-sm text-muted-foreground space-y-1 ml-4">
                        <li v-for="(item, index) in trans('legal.privacy_policy.data_usage.items')" :key="index">{{ item }}</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.privacy_policy.data_retention.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.privacy_policy.data_retention.content') }}</p>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.privacy_policy.user_rights.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.privacy_policy.user_rights.intro') }}</p>
                    <ul class="list-disc list-inside text-sm text-muted-foreground space-y-1 ml-4">
                        <li v-for="(item, index) in trans('legal.privacy_policy.user_rights.items')" :key="index">{{ item }}</li>
                    </ul>
                    <p class="text-sm text-muted-foreground mt-2">{{ trans('legal.privacy_policy.user_rights.footer') }}</p>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.privacy_policy.data_security.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.privacy_policy.data_security.intro') }}</p>
                    <ul class="list-disc list-inside text-sm text-muted-foreground space-y-1 ml-4">
                        <li v-for="(item, index) in trans('legal.privacy_policy.data_security.items')" :key="index">{{ item }}</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.privacy_policy.third_party.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.privacy_policy.third_party.intro') }}</p>
                    <ul class="list-disc list-inside text-sm text-muted-foreground space-y-1 ml-4">
                        <li v-for="(item, index) in trans('legal.privacy_policy.third_party.items')" :key="index">{{ item }}</li>
                    </ul>
                    <p class="text-sm text-muted-foreground mt-2">{{ trans('legal.privacy_policy.third_party.footer') }}</p>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.privacy_policy.international_transfers.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.privacy_policy.international_transfers.intro') }}</p>
                    <ul class="list-disc list-inside text-sm text-muted-foreground space-y-1 ml-4">
                        <li v-for="(item, index) in trans('legal.privacy_policy.international_transfers.items')" :key="index">{{ item }}</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.privacy_policy.cookies.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.privacy_policy.cookies.content') }}</p>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.privacy_policy.changes.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.privacy_policy.changes.content') }}</p>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.privacy_policy.contact.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.privacy_policy.contact.intro') }}</p>
                    <p class="text-sm text-muted-foreground">{{ trans('legal.privacy_policy.contact.email') }}</p>
                </section>
            </div>
        </div>
    </WelcomeLayout>
</template>
