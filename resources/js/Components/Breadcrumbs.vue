<script setup lang="ts">
import { Link } from '@inertiajs/vue3'

export interface BreadcrumbItem {
    label: string
    href?: string
}

defineProps<{
    items: BreadcrumbItem[]
}>()
</script>

<template>
    <nav
        class="flex mb-4"
        aria-label="Breadcrumb"
    >
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li
                v-for="(item, index) in items"
                :key="index"
                class="inline-flex items-center"
            >
                <span
                    v-if="index > 0"
                    class="mx-2 text-gray-400"
                >
                    /
                </span>
                <Link
                    v-if="item.href"
                    :href="item.href"
                    class="text-sm font-medium text-primary hover:underline"
                >
                    {{ item.label }}
                </Link>
                <span
                    v-else
                    class="text-sm font-medium text-gray-500"
                >
                    {{ item.label }}
                </span>
            </li>
        </ol>
    </nav>
</template>
