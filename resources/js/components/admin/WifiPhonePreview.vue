<script setup lang="ts">
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    ssid: string;
    /** Accent colour for the header band and primary button (hex). */
    accent?: string;
}>();

const { t } = useI18n();

const accent = computed(() => props.accent || '#f97316');
const networkName = computed(() => props.ssid.trim() || t('qrcodes.wifi.preview_placeholder_ssid'));
</script>

<template>
    <div class="flex flex-col items-center gap-2">
        <span class="text-xs font-medium text-muted-foreground">{{ t('qrcodes.wifi.preview_title') }}</span>

        <!-- Phone frame -->
        <div class="relative h-[480px] w-[240px] rounded-[2.5rem] border-[6px] border-neutral-800 bg-neutral-800 shadow-xl">
            <div class="relative flex h-full w-full flex-col overflow-hidden rounded-[2rem] bg-white">
                <!-- Status bar + notch -->
                <div class="absolute inset-x-0 top-0 z-10 flex items-center justify-between px-5 pt-2 text-[10px] font-semibold text-white">
                    <span>9:41</span>
                    <span class="h-5 w-20 rounded-full bg-neutral-900" />
                    <span class="flex items-center gap-1">
                        <svg class="h-2.5 w-3" viewBox="0 0 12 10" fill="currentColor"><rect x="0" y="6" width="2" height="4" rx="0.5"/><rect x="3.3" y="4" width="2" height="6" rx="0.5"/><rect x="6.6" y="2" width="2" height="8" rx="0.5"/><rect x="10" y="0" width="2" height="10" rx="0.5"/></svg>
                        <svg class="h-2.5 w-4" viewBox="0 0 20 10" fill="currentColor"><rect x="0.5" y="0.5" width="16" height="9" rx="2" fill="none" stroke="currentColor"/><rect x="2" y="2" width="12" height="6" rx="1"/><rect x="17.5" y="3" width="1.5" height="4" rx="0.5"/></svg>
                    </span>
                </div>

                <!-- Accent band -->
                <div class="h-2/3 w-full" :style="{ backgroundColor: accent }" />

                <!-- Join dialog card -->
                <div class="absolute inset-x-4 top-12 flex flex-col items-center gap-5 rounded-lg bg-white px-4 pt-8 pb-6 text-center shadow-sm">
                    <svg class="h-20 w-20 opacity-25" viewBox="0 0 24 24" fill="none" :stroke="accent" stroke-width="2" stroke-linecap="round">
                        <path d="M2 8.82a15 15 0 0 1 20 0" />
                        <path d="M5 12.86a10 10 0 0 1 14 0" />
                        <path d="M8.5 16.43a5 5 0 0 1 7 0" />
                        <circle cx="12" cy="20" r="0.6" :fill="accent" />
                    </svg>
                    <p class="text-sm leading-snug font-semibold text-neutral-900">
                        {{ t('qrcodes.wifi.preview_question', { ssid: networkName }) }}
                    </p>
                </div>

                <!-- Buttons -->
                <div class="absolute inset-x-4 bottom-8 flex flex-col gap-2">
                    <div class="rounded-md py-2 text-center text-sm font-medium text-white" :style="{ backgroundColor: accent }">
                        {{ t('qrcodes.wifi.preview_connect') }}
                    </div>
                    <div class="rounded-md border py-2 text-center text-sm" :style="{ borderColor: accent, color: accent }">
                        {{ t('qrcodes.wifi.preview_close') }}
                    </div>
                </div>

                <!-- Home indicator -->
                <span class="absolute bottom-2 left-1/2 h-1 w-24 -translate-x-1/2 rounded-full bg-neutral-900" />
            </div>
        </div>
    </div>
</template>
