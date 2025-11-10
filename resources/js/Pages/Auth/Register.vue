<script setup lang="ts">
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import type { RegisterPageProps } from '@/types/auth'
import { useTrans } from '@/composables/useTrans'

const props = defineProps<RegisterPageProps>()
const page = usePage()
const locale = computed(() => (page.props.locale as string) || 'en')
const trans = useTrans()

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms_accepted: false,
    not_testament_acknowledged: false,
})

const submit = (): void => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <Head :title="trans('auth.register.page_title')" />

    <GuestLayout
        :title="trans('auth.register.title')"
        :description="trans('auth.register.description')"
    >
        <form @submit.prevent="submit">
            <div class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="name">{{ trans('auth.register.name') }}</Label>
                    <Input
                        id="name"
                        type="text"
                        v-model="form.name"
                        :placeholder="trans('auth.register.name_placeholder')"
                        required
                        autofocus
                        autocomplete="name"
                        :disabled="form.processing"
                    />
                    <p v-if="form.errors.name" class="text-sm font-medium text-destructive">
                        {{ form.errors.name }}
                    </p>
                </div>

                <div class="grid gap-2">
                    <Label for="email">{{ trans('auth.register.email') }}</Label>
                    <Input
                        id="email"
                        type="email"
                        v-model="form.email"
                        :placeholder="trans('auth.register.email_placeholder')"
                        required
                        autocomplete="email"
                        :disabled="form.processing"
                    />
                    <p v-if="form.errors.email" class="text-sm font-medium text-destructive">
                        {{ form.errors.email }}
                    </p>
                </div>

                <div class="grid gap-2">
                    <Label for="password">{{ trans('auth.register.password') }}</Label>
                    <Input
                        id="password"
                        type="password"
                        v-model="form.password"
                        :placeholder="trans('auth.register.password_placeholder')"
                        required
                        autocomplete="new-password"
                        :disabled="form.processing"
                        minlength="8"
                    />
                    <p v-if="form.errors.password" class="text-sm font-medium text-destructive">
                        {{ form.errors.password }}
                    </p>
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">{{ trans('auth.register.confirm_password') }}</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        v-model="form.password_confirmation"
                        :placeholder="trans('auth.register.confirm_password_placeholder')"
                        required
                        autocomplete="new-password"
                        :disabled="form.processing"
                        minlength="8"
                    />
                    <p v-if="form.errors.password_confirmation" class="text-sm font-medium text-destructive">
                        {{ form.errors.password_confirmation }}
                    </p>
                </div>

                <div class="grid gap-4 pt-2">
                    <div class="flex items-start space-x-2">
                        <input
                            type="checkbox"
                            id="terms_accepted"
                            v-model="form.terms_accepted"
                            :disabled="form.processing"
                            class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary mt-0.5"
                        />
                        <Label
                            for="terms_accepted"
                            class="text-sm font-normal leading-tight cursor-pointer"
                        >
                            {{ trans('auth.register.terms_prefix') }}
                            <Link
                                :href="route(`legal.privacy.${locale}`)"
                                class="text-primary underline hover:no-underline"
                                target="_blank"
                            >
                                {{ trans('auth.register.privacy_policy') }}
                            </Link>
                            {{ trans('auth.register.and') }}
                            <Link
                                :href="route(`legal.terms.${locale}`)"
                                class="text-primary underline hover:no-underline"
                                target="_blank"
                            >
                                {{ trans('auth.register.terms_of_service') }}
                            </Link>
                        </Label>
                    </div>
                    <p v-if="form.errors.terms_accepted" class="text-sm font-medium text-destructive -mt-2">
                        {{ form.errors.terms_accepted }}
                    </p>

                    <div class="flex items-start space-x-2">
                        <input
                            type="checkbox"
                            id="not_testament_acknowledged"
                            v-model="form.not_testament_acknowledged"
                            :disabled="form.processing"
                            class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary mt-0.5"
                        />
                        <Label
                            for="not_testament_acknowledged"
                            class="text-sm font-normal leading-tight cursor-pointer"
                            v-html="trans('auth.register.not_testament') + ' (' + `<a href='${route(`legal.disclaimer.${locale}`)}' class='text-primary underline hover:no-underline' target='_blank'>${trans('auth.register.read_disclaimer')}</a>` + ')'"
                        >
                        </Label>
                    </div>
                    <p v-if="form.errors.not_testament_acknowledged" class="text-sm font-medium text-destructive -mt-2">
                        {{ form.errors.not_testament_acknowledged }}
                    </p>
                </div>

                <Button
                    type="submit"
                    variant="default"
                    class="w-full"
                    :disabled="form.processing"
                >
                    <span v-if="form.processing">{{ trans('auth.register.creating_account') }}</span>
                    <span v-else>{{ trans('auth.register.create_account') }}</span>
                </Button>
            </div>
        </form>

        <div class="mt-4 text-center text-sm">
            {{ trans('auth.register.have_account') }}
            <Link
                :href="route('login')"
                class="underline"
            >
                {{ trans('auth.register.sign_in') }}
            </Link>
        </div>
    </GuestLayout>
</template>
