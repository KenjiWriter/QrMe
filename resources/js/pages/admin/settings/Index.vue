<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

const props = defineProps<{
    settings: { company_name: string | null; vat_id: string | null; qr_color: string | null };
}>();

const { t } = useI18n();

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
    qr_color: props.settings.qr_color ?? '#000000',
});

function submit() {
    form.put('/admin/settings', { preserveScroll: true });
}
</script>

<template>
    <Head :title="t('settings.title')" />

    <div class="flex flex-col gap-6 p-4">
        <Heading
            variant="small"
            :title="t('settings.company_details')"
            :description="t('settings.description')"
        />

        <form @submit.prevent="submit" class="grid max-w-xl gap-6">
            <div class="grid gap-2">
                <Label for="company_name">{{ t('settings.fields.company_name') }}</Label>
                <Input id="company_name" v-model="form.company_name" required />
                <InputError :message="form.errors.company_name" />
            </div>

            <div class="grid gap-2">
                <Label for="vat_id">{{ t('settings.fields.vat_id') }}</Label>
                <Input id="vat_id" v-model="form.vat_id" required />
                <InputError :message="form.errors.vat_id" />
            </div>

            <div class="grid gap-2">
                <Label for="qr_color">{{ t('settings.fields.qr_color') }}</Label>
                <div class="flex items-center gap-3">
                    <input
                        id="qr_color"
                        type="color"
                        v-model="form.qr_color"
                        class="h-10 w-14 cursor-pointer rounded-md border border-input bg-background p-1"
                    />
                    <Input
                        v-model="form.qr_color"
                        class="w-32 font-mono uppercase"
                        maxlength="7"
                        pattern="^#[0-9A-Fa-f]{6}$"
                    />
                </div>
                <InputError :message="form.errors.qr_color" />
            </div>

            <div>
                <Button type="submit" :disabled="form.processing">
                    <Spinner v-if="form.processing" />
                    {{ t('settings.save') }}
                </Button>
            </div>
        </form>
    </div>
</template>
