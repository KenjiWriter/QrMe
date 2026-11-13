<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Link2, Wifi } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import QrStylePicker from '@/components/admin/QrStylePicker.vue';
import WifiPhonePreview from '@/components/admin/WifiPhonePreview.vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import type { CustomQrItem, CustomQrType, WifiEapMethod, WifiEncryption } from '@/types';

const props = defineProps<{
    item?: Partial<CustomQrItem> | null;
}>();

const { t } = useI18n();

const isEdit = computed(() => !!props.item?.id);
const removeLogo = ref(false);

const form = useForm({
    name:              props.item?.name              ?? '',
    type:              (props.item?.type             ?? 'url') as CustomQrType,
    url:               props.item?.url               ?? '',
    wifi_ssid:         props.item?.wifi_ssid         ?? '',
    wifi_password:     props.item?.wifi_password     ?? '',
    wifi_encryption:   (props.item?.wifi_encryption  ?? 'WPA') as WifiEncryption,
    wifi_hidden:       props.item?.wifi_hidden       ?? false,
    wifi_eap_method:   (props.item?.wifi_eap_method  ?? 'PEAP') as WifiEapMethod,
    wifi_eap_identity: props.item?.wifi_eap_identity ?? '',
    qr_color:          props.item?.qr_color          ?? '#000000',
    qr_eye_shape:      props.item?.qr_eye_shape      ?? 'square',
    logo:              null as File | null,
    remove_logo:       false,
    _method:           isEdit.value ? 'put' : 'post',
});

const isWifi = computed(() => form.type === 'wifi');
const needsPassword = computed(() => form.wifi_encryption !== 'nopass');
const isEap = computed(() => form.wifi_encryption === 'WPA-EAP');

const types = [
    { value: 'url',  label: 'qrcodes.types.url',  hint: 'qrcodes.types.url_hint',  icon: Link2 },
    { value: 'wifi', label: 'qrcodes.types.wifi', hint: 'qrcodes.types.wifi_hint', icon: Wifi },
] as const;

const encryptions = computed<{ value: WifiEncryption; label: string }[]>(() => [
    { value: 'WPA',     label: 'WPA / WPA2 / WPA3' },
    { value: 'WEP',     label: 'WEP' },
    { value: 'WPA-EAP', label: 'WPA-EAP (Enterprise)' },
    { value: 'nopass',  label: t('qrcodes.wifi.encryption_none') },
]);

const eapMethods: WifiEapMethod[] = ['PEAP', 'TTLS', 'PWD'];

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

</script>

<template>
    <div class="flex flex-col gap-8 lg:flex-row lg:items-start">
        <form @submit.prevent="submit" class="grid w-full max-w-2xl gap-6">
            <!-- QR type -->
            <div class="grid gap-2">
                <Label>{{ t('qrcodes.fields.type') }}</Label>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <button
                        v-for="ty in types"
                        :key="ty.value"
                        type="button"
                        :class="[
                            'flex items-start gap-3 rounded-lg border p-3 text-left transition-colors',
                            form.type === ty.value
                                ? 'border-primary bg-primary/10'
                                : 'border-input hover:border-primary/50',
                        ]"
                        :aria-pressed="form.type === ty.value"
                        @click="form.type = ty.value"
                    >
                        <component
                            :is="ty.icon"
                            :class="['mt-0.5 size-5 shrink-0', form.type === ty.value ? 'text-primary' : 'text-muted-foreground']"
                        />
                        <span class="flex flex-col gap-0.5">
                            <span class="text-sm font-medium">{{ t(ty.label) }}</span>
                            <span class="text-xs text-muted-foreground">{{ t(ty.hint) }}</span>
                        </span>
                    </button>
                </div>
                <InputError :message="form.errors.type" />
            </div>

            <!-- Name -->
            <div class="grid gap-2">
                <Label for="name">{{ t('qrcodes.fields.name') }}</Label>
                <Input
                    id="name"
                    v-model="form.name"
                    required
                    autofocus
                    :placeholder="isWifi ? t('qrcodes.wifi.name_placeholder') : t('qrcodes.fields.name_placeholder')"
                />
                <InputError :message="form.errors.name" />
            </div>

            <!-- Target URL -->
            <div v-if="!isWifi" class="grid gap-2">
                <Label for="url">{{ t('qrcodes.fields.url') }}</Label>
                <Input id="url" v-model="form.url" type="url" required placeholder="https://..." />
                <p class="text-xs text-muted-foreground">{{ t('qrcodes.fields.url_hint') }}</p>
                <InputError :message="form.errors.url" />
            </div>

            <!-- Wi-Fi network -->
            <fieldset v-else class="grid gap-4 border-t pt-4">
                <legend class="px-2 text-sm font-medium">{{ t('qrcodes.wifi.section') }}</legend>

                <div class="grid gap-2">
                    <Label for="wifi_ssid">{{ t('qrcodes.wifi.ssid') }}</Label>
                    <Input
                        id="wifi_ssid"
                        v-model="form.wifi_ssid"
                        required
                        maxlength="32"
                        autocomplete="off"
                        :placeholder="t('qrcodes.wifi.ssid_placeholder')"
                    />
                    <InputError :message="form.errors.wifi_ssid" />
                </div>

                <div v-if="isEap" class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="wifi_eap_method">{{ t('qrcodes.wifi.eap_method') }}</Label>
                        <select
                            id="wifi_eap_method"
                            v-model="form.wifi_eap_method"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm"
                        >
                            <option v-for="m in eapMethods" :key="m" :value="m">{{ m }}</option>
                        </select>
                        <InputError :message="form.errors.wifi_eap_method" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="wifi_eap_identity">{{ t('qrcodes.wifi.eap_identity') }}</Label>
                        <Input
                            id="wifi_eap_identity"
                            v-model="form.wifi_eap_identity"
                            required
                            autocomplete="off"
                            :placeholder="t('qrcodes.wifi.eap_identity_placeholder')"
                        />
                        <InputError :message="form.errors.wifi_eap_identity" />
                    </div>
                </div>

                <div v-if="needsPassword" class="grid gap-2">
                    <Label for="wifi_password">{{ t('qrcodes.wifi.password') }}</Label>
                    <PasswordInput
                        id="wifi_password"
                        v-model="form.wifi_password"
                        required
                        autocomplete="new-password"
                        :minlength="form.wifi_encryption === 'WPA' ? 8 : undefined"
                        :maxlength="form.wifi_encryption === 'WPA' ? 63 : 255"
                        :placeholder="t('qrcodes.wifi.password_placeholder')"
                    />
                    <InputError :message="form.errors.wifi_password" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2 sm:items-end">
                    <div class="grid gap-2">
                        <Label for="wifi_encryption">{{ t('qrcodes.wifi.encryption') }}</Label>
                        <select
                            id="wifi_encryption"
                            v-model="form.wifi_encryption"
                            class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm"
                        >
                            <option v-for="e in encryptions" :key="e.value" :value="e.value">{{ e.label }}</option>
                        </select>
                        <InputError :message="form.errors.wifi_encryption" />
                    </div>

                    <Label for="wifi_hidden" class="flex h-9 cursor-pointer items-center gap-2 rounded-md border border-input px-3 font-normal">
                        <Checkbox id="wifi_hidden" v-model="form.wifi_hidden" />
                        {{ t('qrcodes.wifi.hidden') }}
                    </Label>
                </div>

                <p class="text-xs text-muted-foreground">{{ t('qrcodes.wifi.hint') }}</p>
            </fieldset>

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

        <!-- Live preview of what a phone shows after scanning a Wi-Fi code -->
        <aside v-if="isWifi" class="hidden lg:sticky lg:top-4 lg:block">
            <WifiPhonePreview :ssid="form.wifi_ssid" :accent="form.qr_color" />
        </aside>
    </div>
</template>
