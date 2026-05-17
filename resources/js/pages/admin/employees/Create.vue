<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import EmployeeForm from '@/components/admin/EmployeeForm.vue';
import Heading from '@/components/Heading.vue';

defineProps<{
    locations: { id: number; name: string }[];
    emailDomain: string;
}>();

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
    <Head title="New employee" />

    <div class="flex flex-col gap-6 p-4">
        <Heading variant="small" title="New employee" description="Create a digital business card." />
        <EmployeeForm :locations="locations" :email-domain="emailDomain" />
    </div>
</template>
