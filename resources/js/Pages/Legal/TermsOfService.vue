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
    <Head :title="trans('legal.terms_of_service.title')" />

    <WelcomeLayout :can-login="canLogin" :can-register="canRegister">
        <div class="mx-auto max-w-4xl px-6 py-16 sm:py-24 lg:px-8">
            <h1 class="text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl mb-4">
                {{ trans('legal.terms_of_service.title') }}
            </h1>

            <div class="prose prose-slate max-w-none">
                <p class="text-sm text-muted-foreground mb-6">
                    {{ trans('legal.terms_of_service.last_updated') }}: {{ lastUpdated }}
                </p>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.terms_of_service.acceptance.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.terms_of_service.acceptance.content') }}</p>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.terms_of_service.service_description.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.terms_of_service.service_description.intro') }}</p>
                    <ul class="list-disc list-inside text-sm text-muted-foreground space-y-1 ml-4">
                        <li v-for="(item, index) in trans('legal.terms_of_service.service_description.items')" :key="index">{{ item }}</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.terms_of_service.disclaimer.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.terms_of_service.disclaimer.intro') }}</p>
                    <ul class="list-disc list-inside text-sm text-muted-foreground space-y-1 ml-4">
                        <li v-for="(item, index) in trans('legal.terms_of_service.disclaimer.items')" :key="index">{{ item }}</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.terms_of_service.user_responsibilities.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.terms_of_service.user_responsibilities.intro') }}</p>
                    <ul class="list-disc list-inside text-sm text-muted-foreground space-y-1 ml-4">
                        <li v-for="(item, index) in trans('legal.terms_of_service.user_responsibilities.items')" :key="index">{{ item }}</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.terms_of_service.free_plan.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.terms_of_service.free_plan.intro') }}</p>
                    <ul class="list-disc list-inside text-sm text-muted-foreground space-y-1 ml-4">
                        <li v-for="(item, index) in trans('legal.terms_of_service.free_plan.items')" :key="index">{{ item }}</li>
                    </ul>
                    <p class="text-sm text-muted-foreground mt-2">{{ trans('legal.terms_of_service.free_plan.footer') }}</p>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.terms_of_service.service_availability.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.terms_of_service.service_availability.intro') }}</p>
                    <ul class="list-disc list-inside text-sm text-muted-foreground space-y-1 ml-4">
                        <li v-for="(item, index) in trans('legal.terms_of_service.service_availability.items')" :key="index">{{ item }}</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.terms_of_service.email_delivery.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.terms_of_service.email_delivery.intro') }}</p>
                    <ul class="list-disc list-inside text-sm text-muted-foreground space-y-1 ml-4">
                        <li v-for="(item, index) in trans('legal.terms_of_service.email_delivery.items')" :key="index">{{ item }}</li>
                    </ul>
                    <p class="text-sm text-muted-foreground mt-2">{{ trans('legal.terms_of_service.email_delivery.footer') }}</p>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.terms_of_service.content_ownership.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.terms_of_service.content_ownership.intro') }}</p>
                    <ul class="list-disc list-inside text-sm text-muted-foreground space-y-1 ml-4">
                        <li v-for="(item, index) in trans('legal.terms_of_service.content_ownership.items')" :key="index">{{ item }}</li>
                    </ul>
                    <p class="text-sm text-muted-foreground mt-2">{{ trans('legal.terms_of_service.content_ownership.footer') }}</p>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.terms_of_service.termination.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.terms_of_service.termination.intro') }}</p>
                    <ul class="list-disc list-inside text-sm text-muted-foreground space-y-1 ml-4">
                        <li v-for="(item, index) in trans('legal.terms_of_service.termination.items')" :key="index">{{ item }}</li>
                    </ul>
                    <p class="text-sm text-muted-foreground mt-2">{{ trans('legal.terms_of_service.termination.footer') }}</p>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.terms_of_service.liability.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.terms_of_service.liability.intro') }}</p>
                    <ul class="list-disc list-inside text-sm text-muted-foreground space-y-1 ml-4">
                        <li v-for="(item, index) in trans('legal.terms_of_service.liability.items')" :key="index">{{ item }}</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.terms_of_service.indemnification.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.terms_of_service.indemnification.intro') }}</p>
                    <ul class="list-disc list-inside text-sm text-muted-foreground space-y-1 ml-4">
                        <li v-for="(item, index) in trans('legal.terms_of_service.indemnification.items')" :key="index">{{ item }}</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.terms_of_service.governing_law.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.terms_of_service.governing_law.content') }}</p>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.terms_of_service.changes.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.terms_of_service.changes.content') }}</p>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.terms_of_service.contact.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.terms_of_service.contact.intro') }}</p>
                    <p class="text-sm text-muted-foreground">{{ trans('legal.terms_of_service.contact.email') }}</p>
                </section>
            </div>
        </div>
    </WelcomeLayout>
</template>
