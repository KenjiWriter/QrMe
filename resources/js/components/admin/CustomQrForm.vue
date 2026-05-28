<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import InputError from '@/components/InputError.vue';
import QrStylePicker from '@/components/admin/QrStylePicker.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

type ItemData = {
    id?: number;
    short_id?: string;
    name: string;
    url: string;
    qr_color: string;
    qr_eye_shape: string;
    logo_url?: string | null;
};

const props = defineProps<{
    item?: ItemData | null;
}>();

const isEdit = computed(() => !!props.item?.id);
const removeLogo = ref(false);

const form = useForm({
    name:          props.item?.name         ?? '',
    url:           props.item?.url          ?? '',
    qr_color:      props.item?.qr_color     ?? '#000000',
    qr_eye_shape:  props.item?.qr_eye_shape ?? 'square',
    logo:          null as File | null,
    remove_logo:   false,
    _method:       isEdit.value ? 'put' : 'post',
});

function onLogoChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0] ?? null;
    form.logo = file;
    removeLogo.value = false;
    form.remove_logo = false;
}

function onRemoveLogo() {
    removeLogo.value = true;
    form.remove_logo = true;
    form.logo = null;
}

function submit() {
    if (isEdit.value && props.item?.short_id) {
        form.post(`/admin/qrcodes/${props.item.short_id}`, { forceFormData: true });
    } else {
        form.post('/admin/qrcodes', { forceFormData: true });
    }
}

const { t } = useI18n();
</script>

<template>
    <form @submit.prevent="submit" class="grid max-w-2xl gap-6">
        <!-- Name -->
        <div class="grid gap-2">
            <Label for="name">{{ t('qrcodes.fields.name') }}</Label>
            <Input id="name" v-model="form.name" required autofocus :placeholder="t('qrcodes.fields.name_placeholder')" />
            <InputError :message="form.errors.name" />
        </div>

        <!-- Target URL -->
        <div class="grid gap-2">
            <Label for="url">{{ t('qrcodes.fields.url') }}</Label>
            <Input id="url" v-model="form.url" type="url" required placeholder="https://..." />
            <p class="text-xs text-muted-foreground">{{ t('qrcodes.fields.url_hint') }}</p>
            <InputError :message="form.errors.url" />
        </div>

        <!-- QR style (color + eye shape) -->
        <QrStylePicker
            v-model:color="form.qr_color"
            v-model:eye-shape="form.qr_eye_shape"
            :required="true"
            :color-error="form.errors.qr_color"
            :eye-shape-error="form.errors.qr_eye_shape"
        />

        <!-- Logo upload -->
        <fieldset class="grid gap-4 border-t pt-4">
            <legend class="px-2 text-sm font-medium">{{ t('qrcodes.fields.logo_section') }}</legend>
            <div class="grid gap-2">
                <Label for="logo">{{ t('qrcodes.fields.logo') }}</Label>

                <!-- Existing logo preview -->
                <div v-if="item?.logo_url && !removeLogo" class="flex items-center gap-3">
                    <img :src="item.logo_url" class="h-16 w-16 rounded border object-contain p-1" alt="logo" />
                    <button type="button" class="text-xs text-destructive underline" @click="onRemoveLogo">
                        {{ t('qrcodes.fields.remove_logo') }}
                    </button>
                </div>

                <Input id="logo" type="file" accept="image/png,image/jpeg,image/jpg,image/svg+xml" @change="onLogoChange" />
                <p class="text-xs text-muted-foreground">{{ t('qrcodes.fields.logo_hint') }}</p>
                <InputError :message="form.errors.logo" />
            </div>
        </fieldset>

        <div class="flex justify-end gap-2">
            <Button type="submit" :disabled="form.processing">
                <Spinner v-if="form.processing" />
                {{ isEdit ? t('qrcodes.save_changes') : t('qrcodes.create_submit') }}
            </Button>
        </div>
    </form>
</template>
