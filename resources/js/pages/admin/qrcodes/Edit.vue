<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import CustomQrForm from '@/components/admin/CustomQrForm.vue';
import Heading from '@/components/Heading.vue';
import type { CustomQrItem } from '@/types';

const props = defineProps<{
    item: CustomQrItem;
}>();

const { t } = useI18n();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: '/admin/employees' },
            { title: 'QR Codes', href: '/admin/qrcodes' },
            { title: 'Edit', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="`${t('qrcodes.edit_title')} — ${props.item.name}`" />

    <div class="flex flex-col gap-6 p-4">
        <Heading
            variant="small"
            :title="`${t('qrcodes.edit_title')} — ${props.item.name}`"
            :description="t('qrcodes.edit_description')"
        />

        <!-- QR preview -->
        <div v-if="props.item.qr_code_url" class="flex items-center gap-3 rounded-xl border p-3 max-w-md">
            <img :src="props.item.qr_code_url" class="h-20 w-20" alt="QR" />
            <div class="text-xs flex flex-col gap-1">
                <span v-if="props.item.type === 'wifi'" class="font-medium break-all">
                    {{ t('qrcodes.wifi.network_label', { ssid: props.item.wifi_ssid }) }}
                </span>
                <a v-else :href="props.item.url ?? undefined" target="_blank" class="text-blue-600 hover:underline break-all">
                    {{ props.item.url }}
                </a>
                <span v-if="props.item.type !== 'wifi'" class="text-muted-foreground">
                    {{ t('qrcodes.scans', { count: props.item.scan_count }) }}
                </span>
            </div>
        </div>

        <CustomQrForm :item="props.item" />
    </div>
</template>
