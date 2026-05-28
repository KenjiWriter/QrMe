<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import EmployeeForm from '@/components/admin/EmployeeForm.vue';
import Heading from '@/components/Heading.vue';

defineProps<{
    locations: { id: number; name: string }[];
    emailDomain: string;
    globalQrColor: string;
}>();

const { t } = useI18n();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: '/admin/employees' },
            { title: 'Employees', href: '/admin/employees' },
            { title: 'New', href: '/admin/employees/create' },
        ],
    },
});
</script>

<template>
    <Head :title="t('employees.new_title')" />

    <div class="flex flex-col gap-6 p-4">
        <Heading variant="small" :title="t('employees.new_title')" :description="t('employees.new_description')" />
        <EmployeeForm :locations="locations" :email-domain="emailDomain" :global-qr-color="globalQrColor" />
    </div>
</template>
