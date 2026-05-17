<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

const props = defineProps<{
    settings: { company_name: string | null; vat_id: string | null };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: '/admin/settings' },
            { title: 'Global settings', href: '/admin/settings' },
        ],
    },
});

const form = useForm({
    company_name: props.settings.company_name ?? '',
    vat_id: props.settings.vat_id ?? '',
});

function submit() {
    form.put('/admin/settings', { preserveScroll: true });
}
</script>

<template>
    <Head title="Global settings" />

    <div class="flex flex-col gap-6 p-4">
        <Heading
            variant="small"
            title="Company details"
            description="These details appear on every employee's public business card."
        />

        <form @submit.prevent="submit" class="grid max-w-xl gap-6">
            <div class="grid gap-2">
                <Label for="company_name">Company name</Label>
                <Input id="company_name" v-model="form.company_name" required />
                <InputError :message="form.errors.company_name" />
            </div>

            <div class="grid gap-2">
                <Label for="vat_id">VAT ID / NIP</Label>
                <Input id="vat_id" v-model="form.vat_id" required />
                <InputError :message="form.errors.vat_id" />
            </div>

            <div>
                <Button type="submit" :disabled="form.processing">
                    <Spinner v-if="form.processing" />
                    Save settings
                </Button>
            </div>
        </form>
    </div>
</template>
