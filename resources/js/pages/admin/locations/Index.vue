<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';

type Location = {
    id: number;
    name: string;
    street: string;
    building_number: string;
    apartment_number: string | null;
    city: string;
    postal_code: string;
    country: string;
};

const props = defineProps<{ locations: Location[] }>();
const { t } = useI18n();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: '/admin/locations' },
            { title: 'Locations', href: '/admin/locations' },
        ],
    },
});

const open = ref(false);
const editing = ref<Location | null>(null);

const form = useForm({
    name: '',
    street: '',
    building_number: '',
    apartment_number: '',
    city: '',
    postal_code: '',
    country: 'Polska',
});

function openCreate() {
    editing.value = null;
    form.reset();
    form.country = 'Polska';
    form.clearErrors();
    open.value = true;
}

function openEdit(loc: Location) {
    editing.value = loc;
    form.name = loc.name;
    form.street = loc.street;
    form.building_number = loc.building_number;
    form.apartment_number = loc.apartment_number ?? '';
    form.city = loc.city;
    form.postal_code = loc.postal_code;
    form.country = loc.country;
    form.clearErrors();
    open.value = true;
}

function submit() {
    const onSuccess = () => {
        open.value = false;
    };
    if (editing.value) {
        form.put(`/admin/locations/${editing.value.id}`, { onSuccess, preserveScroll: true });
    } else {
        form.post('/admin/locations', { onSuccess, preserveScroll: true });
    }
}

function destroy(loc: Location) {
    if (!confirm(t('locations.confirm_delete', { name: loc.name }))) return;
    router.delete(`/admin/locations/${loc.id}`, { preserveScroll: true });
}
</script>

<template>
    <Head :title="t('locations.title')" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <Heading
                variant="small"
                :title="t('locations.title')"
                :description="t('locations.description')"
            />
            <Button @click="openCreate">{{ t('locations.add') }}</Button>
        </div>

        <div class="rounded-xl border border-sidebar-border/70 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-left">
                    <tr>
                        <th class="px-4 py-3 font-medium">{{ t('locations.fields.name') }}</th>
                        <th class="px-4 py-3 font-medium">{{ t('locations.fields.address') }}</th>
                        <th class="px-4 py-3 font-medium">{{ t('locations.fields.city') }}</th>
                        <th class="px-4 py-3 text-right font-medium">{{ t('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!props.locations.length">
                        <td colspan="4" class="px-4 py-8 text-center text-muted-foreground">
                            {{ t('locations.empty') }}
                        </td>
                    </tr>
                    <tr
                        v-for="loc in props.locations"
                        :key="loc.id"
                        class="border-t border-sidebar-border/70"
                    >
                        <td class="px-4 py-3 font-medium">{{ loc.name }}</td>
                        <td class="px-4 py-3">
                            {{ loc.street }} {{ loc.building_number }}<span v-if="loc.apartment_number">/{{ loc.apartment_number }}</span>
                        </td>
                        <td class="px-4 py-3">{{ loc.postal_code }} {{ loc.city }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            <Button variant="outline" size="sm" @click="openEdit(loc)">{{ t('common.edit') }}</Button>
                            <Button variant="destructive" size="sm" @click="destroy(loc)">{{ t('common.delete') }}</Button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Dialog v-model:open="open">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ editing ? t('locations.edit') : t('locations.add') }}</DialogTitle>
                    <DialogDescription>{{ t('locations.description') }}</DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submit" class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="name">{{ t('locations.fields.name') }}</Label>
                        <Input id="name" v-model="form.name" required />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-2 grid gap-2">
                            <Label for="street">{{ t('locations.fields.street') }}</Label>
                            <Input id="street" v-model="form.street" required />
                            <InputError :message="form.errors.street" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="building_number">{{ t('locations.fields.building_number') }}</Label>
                            <Input id="building_number" v-model="form.building_number" required />
                            <InputError :message="form.errors.building_number" />
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="grid gap-2">
                            <Label for="apartment_number">{{ t('locations.fields.apartment_number') }}</Label>
                            <Input id="apartment_number" v-model="form.apartment_number" />
                            <InputError :message="form.errors.apartment_number" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="postal_code">{{ t('locations.fields.postal_code') }}</Label>
                            <Input id="postal_code" v-model="form.postal_code" required />
                            <InputError :message="form.errors.postal_code" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="city">{{ t('locations.fields.city') }}</Label>
                            <Input id="city" v-model="form.city" required />
                            <InputError :message="form.errors.city" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="country">{{ t('locations.fields.country') }}</Label>
                        <Input id="country" v-model="form.country" required />
                        <InputError :message="form.errors.country" />
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" @click="open = false">{{ t('common.cancel') }}</Button>
                        <Button type="submit" :disabled="form.processing">
                            <Spinner v-if="form.processing" />
                            {{ editing ? t('common.save') : t('common.create') }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
