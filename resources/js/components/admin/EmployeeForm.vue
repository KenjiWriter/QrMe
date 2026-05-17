<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

type LocationOption = { id: number; name: string };

type EmployeeData = {
    id?: number;
    first_name: string;
    last_name: string;
    job_title: string;
    email: string | null;
    phone: string | null;
    bio: string | null;
    photo_url?: string | null;
    location_id: number | null;
    facebook_url: string | null;
    instagram_url: string | null;
    linkedin_url: string | null;
    youtube_url: string | null;
};

const props = defineProps<{
    locations: LocationOption[];
    emailDomain: string;
    employee?: EmployeeData | null;
}>();

const isEdit = computed(() => !!props.employee?.id);

const form = useForm({
    first_name: props.employee?.first_name ?? '',
    last_name: props.employee?.last_name ?? '',
    job_title: props.employee?.job_title ?? '',
    email: props.employee?.email ?? '',
    phone: props.employee?.phone ?? '',
    bio: props.employee?.bio ?? '',
    photo: null as File | null,
    location_id: props.employee?.location_id ?? null,
    facebook_url: props.employee?.facebook_url ?? '',
    instagram_url: props.employee?.instagram_url ?? '',
    linkedin_url: props.employee?.linkedin_url ?? '',
    youtube_url: props.employee?.youtube_url ?? '',
    _method: isEdit.value ? 'put' : 'post',
});

// Polish-aware diacritic stripping
function stripDiacritics(value: string): string {
    return value.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/ł/g, 'l').replace(/Ł/g, 'L');
}

function autoEmail(first: string, last: string): string {
    if (!first || !last) return '';
    const f = stripDiacritics(first.trim()).toLowerCase().charAt(0);
    const l = stripDiacritics(last.trim()).toLowerCase().replace(/[^a-z0-9]/g, '');
    if (!f || !l) return '';
    return `${f}.${l}@${props.emailDomain}`;
}

// Auto-fill email only when creating (never overwrite manual edits)
let userTouchedEmail = isEdit.value;

watch(
    () => [form.first_name, form.last_name] as const,
    ([first, last]) => {
        if (userTouchedEmail) return;
        form.email = autoEmail(first, last);
    },
);

function onEmailInput() {
    userTouchedEmail = true;
}

function onPhotoChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0] ?? null;
    form.photo = file;
}

function submit() {
    if (isEdit.value && props.employee?.id) {
        form.post(`/admin/employees/${props.employee.id}`, { forceFormData: true });
    } else {
        form.post('/admin/employees', { forceFormData: true });
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="grid max-w-3xl gap-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="grid gap-2">
                <Label for="first_name">First name</Label>
                <Input id="first_name" v-model="form.first_name" required autofocus />
                <InputError :message="form.errors.first_name" />
            </div>
            <div class="grid gap-2">
                <Label for="last_name">Last name</Label>
                <Input id="last_name" v-model="form.last_name" required />
                <InputError :message="form.errors.last_name" />
            </div>
        </div>

        <div class="grid gap-2">
            <Label for="job_title">Job title</Label>
            <Input id="job_title" v-model="form.job_title" required />
            <InputError :message="form.errors.job_title" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="grid gap-2">
                <Label for="email">Email</Label>
                <Input id="email" type="email" v-model="form.email" @input="onEmailInput" required />
                <p class="text-xs text-muted-foreground">
                    Auto-generated. Override if needed.
                </p>
                <InputError :message="form.errors.email" />
            </div>
            <div class="grid gap-2">
                <Label for="phone">Phone</Label>
                <Input id="phone" v-model="form.phone" placeholder="+48 ..." />
                <InputError :message="form.errors.phone" />
            </div>
        </div>

        <div class="grid gap-2">
            <Label for="bio">Bio / short description</Label>
            <textarea
                id="bio"
                v-model="form.bio"
                rows="3"
                class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm"
            />
            <InputError :message="form.errors.bio" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="grid gap-2">
                <Label for="photo">Profile photo</Label>
                <Input id="photo" type="file" accept="image/*" @change="onPhotoChange" />
                <img
                    v-if="employee?.photo_url"
                    :src="employee.photo_url"
                    class="mt-2 h-20 w-20 rounded-full object-cover"
                />
                <InputError :message="form.errors.photo" />
            </div>
            <div class="grid gap-2">
                <Label for="location_id">Location</Label>
                <select
                    id="location_id"
                    v-model="form.location_id"
                    class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm"
                >
                    <option :value="null">— None —</option>
                    <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                </select>
                <InputError :message="form.errors.location_id" />
            </div>
        </div>

        <fieldset class="grid grid-cols-1 md:grid-cols-2 gap-4 border-t pt-4">
            <legend class="px-2 text-sm font-medium">Social links</legend>
            <div class="grid gap-2">
                <Label for="facebook_url">Facebook</Label>
                <Input id="facebook_url" v-model="form.facebook_url" placeholder="https://facebook.com/..." />
                <InputError :message="form.errors.facebook_url" />
            </div>
            <div class="grid gap-2">
                <Label for="instagram_url">Instagram</Label>
                <Input id="instagram_url" v-model="form.instagram_url" placeholder="https://instagram.com/..." />
                <InputError :message="form.errors.instagram_url" />
            </div>
            <div class="grid gap-2">
                <Label for="linkedin_url">LinkedIn</Label>
                <Input id="linkedin_url" v-model="form.linkedin_url" placeholder="https://linkedin.com/in/..." />
                <InputError :message="form.errors.linkedin_url" />
            </div>
            <div class="grid gap-2">
                <Label for="youtube_url">YouTube</Label>
                <Input id="youtube_url" v-model="form.youtube_url" placeholder="https://youtube.com/@..." />
                <InputError :message="form.errors.youtube_url" />
            </div>
        </fieldset>

        <div class="flex justify-end gap-2">
            <Button type="submit" :disabled="form.processing">
                <Spinner v-if="form.processing" />
                {{ isEdit ? 'Save changes' : 'Create employee' }}
            </Button>
        </div>
    </form>
</template>
