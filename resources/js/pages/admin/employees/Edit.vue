<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import EmployeeForm from '@/components/admin/EmployeeForm.vue';
import Heading from '@/components/Heading.vue';

const props = defineProps<{
    employee: {
        id: number;
        uuid: string;
        first_name: string;
        last_name: string;
        job_title: string;
        email: string;
        phone: string | null;
        bio: string | null;
        photo_url: string | null;
        qr_code_url: string | null;
        location_id: number | null;
        facebook_url: string | null;
        instagram_url: string | null;
        linkedin_url: string | null;
        youtube_url: string | null;
        public_url: string;
    };
    locations: { id: number; name: string }[];
    emailDomain: string;
}>();

const { t } = useI18n();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: '/admin/employees' },
            { title: 'Employees', href: '/admin/employees' },
            { title: 'Edit', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="`${t('employees.edit_title')} — ${props.employee.first_name} ${props.employee.last_name}`" />

    <div class="flex flex-col gap-6 p-4">
        <Heading
            variant="small"
            :title="`${t('employees.edit_title')} — ${props.employee.first_name} ${props.employee.last_name}`"
            :description="t('employees.edit_description')"
        />
        <div v-if="props.employee.qr_code_url" class="flex items-center gap-3 rounded-xl border p-3 max-w-md">
            <img :src="props.employee.qr_code_url" class="h-20 w-20" alt="QR" />
            <div class="text-xs">
                <a :href="props.employee.public_url" target="_blank" class="text-blue-600 hover:underline break-all">
                    {{ props.employee.public_url }}
                </a>
            </div>
        </div>
        <EmployeeForm :locations="locations" :email-domain="emailDomain" :employee="employee" />
    </div>
</template>
