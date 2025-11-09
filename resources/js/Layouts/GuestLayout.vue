<script setup lang="ts">
import ApplicationLogo from '@/Components/ApplicationLogo.vue'
import Footer from '@/Components/Footer.vue'
import { Link } from '@inertiajs/vue3'

interface Props {
    title?: string
    description?: string
}

const props = withDefaults(defineProps<Props>(), {
    title: undefined,
    description: undefined,
})
</script>

<template>
    <div class="min-h-screen flex flex-col">
        <div class="flex-1 w-full lg:grid lg:min-h-[600px] lg:grid-cols-1 xl:min-h-[800px]">
            <div class="flex items-center justify-center py-12">
                <div class="mx-auto grid w-full max-w-[350px] gap-6 px-4">
                    <div class="flex justify-center">
                        <Link href="/">
                            <ApplicationLogo class="h-48 w-48 fill-current text-foreground hover:opacity-80 transition-opacity" />
                        </Link>
                    </div>

                    <div v-if="title || description" class="grid gap-2 text-center">
                        <h1 v-if="title" class="text-3xl font-bold">
                            {{ title }}
                        </h1>
                        <p v-if="description" class="text-balance text-muted-foreground">
                            {{ description }}
                        </p>
                    </div>

                    <slot />
                </div>
            </div>
        </div>

        <Footer />
    </div>
</template>
