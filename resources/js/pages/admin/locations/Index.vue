<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
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
    if (!confirm(`Delete location "${loc.name}"?`)) return;
    router.delete(`/admin/locations/${loc.id}`, { preserveScroll: true });
}
</script>

<template>
    <Head title="Locations" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <Heading
                variant="small"
                title="Locations"
                description="Manage company offices and addresses."
            />
            <Button @click="openCreate">Add location</Button>
        </div>

        <div class="rounded-xl border border-sidebar-border/70 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-muted/50 text-left">
                    <tr>
                        <th class="px-4 py-3 font-medium">Name</th>
                        <th class="px-4 py-3 font-medium">Address</th>
                        <th class="px-4 py-3 font-medium">City</th>
                        <th class="px-4 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="!props.locations.length">
                        <td colspan="4" class="px-4 py-8 text-center text-muted-foreground">
                            No locations yet.
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
                            <Button variant="outline" size="sm" @click="openEdit(loc)">Edit</Button>
                            <Button variant="destructive" size="sm" @click="destroy(loc)">Delete</Button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Dialog v-model:open="open">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{{ editing ? 'Edit location' : 'New location' }}</DialogTitle>
                    <DialogDescription>Address details used on employee business cards.</DialogDescription>
                </DialogHeader>

                <form @submit.prevent="submit" class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" v-model="form.name" required />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="col-span-2 grid gap-2">
                            <Label for="street">Street</Label>
                            <Input id="street" v-model="form.street" required />
                            <InputError :message="form.errors.street" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="building_number">Building no.</Label>
                            <Input id="building_number" v-model="form.building_number" required />
                            <InputError :message="form.errors.building_number" />
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div class="grid gap-2">
                            <Label for="apartment_number">Apt. no.</Label>
                            <Input id="apartment_number" v-model="form.apartment_number" />
                            <InputError :message="form.errors.apartment_number" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="postal_code">Postal code</Label>
                            <Input id="postal_code" v-model="form.postal_code" required />
                            <InputError :message="form.errors.postal_code" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="city">City</Label>
                            <Input id="city" v-model="form.city" required />
                            <InputError :message="form.errors.city" />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="country">Country</Label>
                        <Input id="country" v-model="form.country" required />
                        <InputError :message="form.errors.country" />
                    </div>

                    <DialogFooter>
                        <Button type="button" variant="outline" @click="open = false">Cancel</Button>
                        <Button type="submit" :disabled="form.processing">
                            <Spinner v-if="form.processing" />
                            {{ editing ? 'Save changes' : 'Create' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
