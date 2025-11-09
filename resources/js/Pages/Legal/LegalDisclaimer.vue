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
    <Head :title="trans('legal.legal_disclaimer.title')" />

    <WelcomeLayout :can-login="canLogin" :can-register="canRegister">
        <div class="mx-auto max-w-4xl px-6 py-16 sm:py-24 lg:px-8">
            <h1 class="text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl mb-4">
                {{ trans('legal.legal_disclaimer.title') }}
            </h1>

            <div class="prose prose-slate max-w-none">
                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.legal_disclaimer.important.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.legal_disclaimer.important.content') }}</p>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.legal_disclaimer.what_it_is.title') }}</h2>
                    <ul class="list-disc list-inside text-sm text-muted-foreground space-y-2 ml-4">
                        <li v-for="(item, index) in trans('legal.legal_disclaimer.what_it_is.items')" :key="index">{{ item }}</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.legal_disclaimer.what_it_is_not.title') }}</h2>
                    <ul class="list-disc list-inside text-sm text-muted-foreground space-y-2 ml-4">
                        <li v-for="(item, index) in trans('legal.legal_disclaimer.what_it_is_not.items')" :key="index">{{ item }}</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.legal_disclaimer.no_legal_force.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.legal_disclaimer.no_legal_force.intro') }}</p>
                    <ul class="list-disc list-inside text-sm text-muted-foreground space-y-1 ml-4">
                        <li v-for="(item, index) in trans('legal.legal_disclaimer.no_legal_force.items')" :key="index">{{ item }}</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.legal_disclaimer.no_guarantees.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.legal_disclaimer.no_guarantees.intro') }}</p>
                    <ul class="list-disc list-inside text-sm text-muted-foreground space-y-1 ml-4">
                        <li v-for="(item, index) in trans('legal.legal_disclaimer.no_guarantees.items')" :key="index">{{ item }}</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.legal_disclaimer.consult_professionals.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.legal_disclaimer.consult_professionals.intro') }}</p>
                    <ul class="list-disc list-inside text-sm text-muted-foreground space-y-1 ml-4">
                        <li v-for="(item, index) in trans('legal.legal_disclaimer.consult_professionals.items')" :key="index">{{ item }}</li>
                    </ul>
                    <p class="text-sm text-muted-foreground mt-2 font-semibold">{{ trans('legal.legal_disclaimer.consult_professionals.footer') }}</p>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.legal_disclaimer.user_responsibility.title') }}</h2>
                    <p class="text-sm text-muted-foreground mb-2">{{ trans('legal.legal_disclaimer.user_responsibility.intro') }}</p>
                    <ul class="list-disc list-inside text-sm text-muted-foreground space-y-1 ml-4">
                        <li v-for="(item, index) in trans('legal.legal_disclaimer.user_responsibility.items')" :key="index">{{ item }}</li>
                    </ul>
                </section>

                <section class="mb-8">
                    <h2 class="text-xl font-semibold mb-4">{{ trans('legal.legal_disclaimer.liability_limitation.title') }}</h2>
                    <p class="text-sm text-muted-foreground">{{ trans('legal.legal_disclaimer.liability_limitation.content') }}</p>
                </section>
            </div>
        </div>
    </WelcomeLayout>
</template>
