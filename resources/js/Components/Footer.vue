<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3'
import { Separator } from '@/components/ui/separator'
import { computed } from 'vue'
import { useTrans } from '@/composables/useTrans'

const page = usePage()
const locale = computed(() => (page.props.locale as string) || 'en')
const trans = useTrans()
</script>

<template>
    <footer class="border-t mt-auto">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 text-sm text-muted-foreground">
                <div class="text-center sm:text-left">
                    &copy; {{ new Date().getFullYear() }} Just In Case. {{ trans('common.footer.all_rights_reserved') }}.
                </div>

                <nav class="flex flex-wrap justify-center gap-x-4 gap-y-2">
                    <Link
                        :href="route(`legal.privacy.${locale}`)"
                        class="hover:text-foreground transition-colors"
                    >
                        {{ trans('common.footer.privacy_policy') }}
                    </Link>
                    <span class="text-muted-foreground/50">|</span>
                    <Link
                        :href="route(`legal.terms.${locale}`)"
                        class="hover:text-foreground transition-colors"
                    >
                        {{ trans('common.footer.terms_of_service') }}
                    </Link>
                    <span class="text-muted-foreground/50">|</span>
                    <Link
                        :href="route(`legal.disclaimer.${locale}`)"
                        class="hover:text-foreground transition-colors"
                    >
                        {{ trans('common.footer.legal_disclaimer') }}
                    </Link>
                </nav>
            </div>
        </div>
    </footer>
</template>
