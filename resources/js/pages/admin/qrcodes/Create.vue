<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import CustomQrForm from '@/components/admin/CustomQrForm.vue';
import Heading from '@/components/Heading.vue';

const { t } = useI18n();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: '/admin/employees' },
            { title: 'QR Codes', href: '/admin/qrcodes' },
            { title: 'New', href: '/admin/qrcodes/create' },
        ],
    },
});
</script>

<template>
    <Head :title="t('qrcodes.new_title')" />

    <div class="flex flex-col gap-6 p-4">
        <Heading variant="small" :title="t('qrcodes.new_title')" :description="t('qrcodes.new_description')" />
        <CustomQrForm />
    </div>
</template>
