<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import LocaleSwitcher from '@/components/LocaleSwitcher.vue';
import { home } from '@/routes';

const { t, te } = useI18n();

const props = defineProps<{
    title?: string;
    description?: string;
}>();

// Allow auth pages to pass an i18n key (e.g. 'auth.login_title') OR a plain string.
const resolvedTitle = computed(() =>
    props.title && te(props.title) ? t(props.title) : (props.title ?? ''),
);
const resolvedDescription = computed(() =>
    props.description && te(props.description) ? t(props.description) : (props.description ?? ''),
);
</script>

<template>
    <div
        class="relative flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10"
    >
        <div class="absolute top-4 right-4">
            <LocaleSwitcher />
        </div>
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-8">
                <div class="flex flex-col items-center gap-4">
                    <Link
                        :href="home()"
                        class="flex flex-col items-center gap-2 font-medium"
                    >
                        <div
                            class="mb-1 flex h-9 w-9 items-center justify-center rounded-md"
                        >
                            <AppLogoIcon
                                class="size-9 fill-current text-[var(--foreground)] dark:text-white"
                            />
                        </div>
                        <span class="sr-only">{{ resolvedTitle }}</span>
                    </Link>
                    <div class="space-y-2 text-center">
                        <h1 class="text-xl font-medium">{{ resolvedTitle }}</h1>
                        <p class="text-center text-sm text-muted-foreground">
                            {{ resolvedDescription }}
                        </p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>
