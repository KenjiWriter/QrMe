<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import CustomQrForm from '@/components/admin/CustomQrForm.vue';
import Heading from '@/components/Heading.vue';

const props = defineProps<{
    item: {
        id: number;
        short_id: string;
        name: string;
        url: string;
        qr_color: string;
        qr_eye_shape: string;
        logo_url: string | null;
        qr_code_url: string | null;
        scan_url: string;
        scan_count: number;
    };
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
                <a :href="props.item.url" target="_blank" class="text-blue-600 hover:underline break-all">
                    {{ props.item.url }}
                </a>
                <span class="text-muted-foreground">
                    {{ t('qrcodes.scans', { count: props.item.scan_count }) }}
                </span>
            </div>
        </div>

        <CustomQrForm :item="props.item" />
    </div>
</template>
