<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import { setLocale, type Locale, SUPPORTED_LOCALES } from '@/i18n';

withDefaults(
    defineProps<{
        variant?: 'sidebar' | 'floating';
    }>(),
    { variant: 'sidebar' },
);

const { locale } = useI18n();

function pick(value: Locale) {
    setLocale(value);
}
</script>

<template>
    <div
        :class="[
            'inline-flex items-center gap-0.5 rounded-md border border-sidebar-border/70 bg-sidebar p-0.5 text-xs font-medium',
            variant === 'floating'
                ? 'bg-white/90 backdrop-blur-sm shadow-sm border-white/40'
                : '',
        ]"
        role="group"
        aria-label="Language switcher"
    >
        <button
            v-for="code in SUPPORTED_LOCALES"
            :key="code"
            type="button"
            class="rounded px-2 py-1 uppercase transition-colors"
            :class="
                locale === code
                    ? 'bg-primary text-primary-foreground'
                    : variant === 'floating'
                      ? 'text-slate-700 hover:bg-white'
                      : 'text-sidebar-foreground hover:bg-sidebar-accent'
            "
            :aria-pressed="locale === code"
            @click="pick(code)"
        >
            {{ code }}
        </button>
    </div>
</template>
