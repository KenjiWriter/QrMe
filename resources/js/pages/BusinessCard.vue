<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Facebook, Instagram, Linkedin, Mail, MapPin, Phone, Youtube, UserPlus } from 'lucide-vue-next';

type Employee = {
    uuid: string;
    first_name: string;
    last_name: string;
    full_name: string;
    job_title: string;
    email: string;
    phone: string | null;
    bio: string | null;
    photo_url: string | null;
    facebook_url: string | null;
    instagram_url: string | null;
    linkedin_url: string | null;
    youtube_url: string | null;
    location: { name: string; full_address: string; maps_url: string } | null;
};

const props = defineProps<{
    employee: Employee;
    company: { name: string | null; vat_id: string | null };
    vcardUrl: string;
}>();

const initials = computed(() => {
    return (props.employee.first_name.charAt(0) + props.employee.last_name.charAt(0)).toUpperCase();
});

const socials = computed(() =>
    [
        { url: props.employee.facebook_url, icon: Facebook, label: 'Facebook' },
        { url: props.employee.instagram_url, icon: Instagram, label: 'Instagram' },
        { url: props.employee.linkedin_url, icon: Linkedin, label: 'LinkedIn' },
        { url: props.employee.youtube_url, icon: Youtube, label: 'YouTube' },
    ].filter((s) => !!s.url),
);
</script>

<template>
    <Head :title="props.employee.full_name" />

    <div class="min-h-svh bg-gradient-to-b from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-950">
        <div class="mx-auto max-w-md px-4 py-8">
            <div class="rounded-3xl bg-white dark:bg-slate-900 shadow-xl overflow-hidden">
                <!-- Hero -->
                <div class="bg-gradient-to-br from-slate-800 to-slate-900 h-32 relative">
                    <div class="absolute -bottom-12 left-1/2 -translate-x-1/2">
                        <img
                            v-if="props.employee.photo_url"
                            :src="props.employee.photo_url"
                            :alt="props.employee.full_name"
                            class="h-24 w-24 rounded-full object-cover border-4 border-white dark:border-slate-900 shadow"
                        />
                        <div
                            v-else
                            class="h-24 w-24 rounded-full bg-slate-200 dark:bg-slate-700 border-4 border-white dark:border-slate-900 shadow flex items-center justify-center text-2xl font-semibold text-slate-600 dark:text-slate-200"
                        >
                            {{ initials }}
                        </div>
                    </div>
                </div>

                <!-- Name & title -->
                <div class="pt-16 pb-6 px-6 text-center">
                    <h1 class="text-xl font-semibold text-slate-900 dark:text-white">
                        {{ props.employee.full_name }}
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        {{ props.employee.job_title }}
                    </p>
                    <p v-if="props.company.name" class="text-xs text-slate-400 mt-1">
                        {{ props.company.name }}
                    </p>
                </div>

                <!-- Action buttons -->
                <div class="px-6 grid grid-cols-2 gap-3">
                    <a
                        :href="`mailto:${props.employee.email}`"
                        class="flex items-center justify-center gap-2 rounded-xl bg-slate-900 dark:bg-white dark:text-slate-900 text-white px-4 py-3 text-sm font-medium active:scale-95 transition"
                    >
                        <Mail class="h-4 w-4" />
                        Email
                    </a>
                    <a
                        v-if="props.employee.phone"
                        :href="`tel:${props.employee.phone}`"
                        class="flex items-center justify-center gap-2 rounded-xl bg-slate-900 dark:bg-white dark:text-slate-900 text-white px-4 py-3 text-sm font-medium active:scale-95 transition"
                    >
                        <Phone class="h-4 w-4" />
                        Phone
                    </a>
                    <a
                        v-if="props.employee.location"
                        :href="props.employee.location.maps_url"
                        target="_blank"
                        rel="noopener"
                        class="flex items-center justify-center gap-2 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white px-4 py-3 text-sm font-medium active:scale-95 transition"
                    >
                        <MapPin class="h-4 w-4" />
                        Maps
                    </a>
                    <a
                        :href="props.vcardUrl"
                        class="flex items-center justify-center gap-2 rounded-xl bg-emerald-600 text-white px-4 py-3 text-sm font-medium active:scale-95 transition col-span-2"
                    >
                        <UserPlus class="h-4 w-4" />
                        Dodaj kontakt
                    </a>
                </div>

                <!-- Bio -->
                <div v-if="props.employee.bio" class="px-6 py-6">
                    <p class="text-sm text-slate-700 dark:text-slate-300 whitespace-pre-line">
                        {{ props.employee.bio }}
                    </p>
                </div>

                <!-- Social links -->
                <div v-if="socials.length" class="px-6 pb-6">
                    <div class="flex justify-center gap-4">
                        <a
                            v-for="s in socials"
                            :key="s.label"
                            :href="s.url!"
                            target="_blank"
                            rel="noopener"
                            :aria-label="s.label"
                            class="h-10 w-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-700 dark:text-slate-200 active:scale-95 transition"
                        >
                            <component :is="s.icon" class="h-5 w-5" />
                        </a>
                    </div>
                </div>

                <!-- Location -->
                <div
                    v-if="props.employee.location"
                    class="border-t border-slate-100 dark:border-slate-800 px-6 py-4 text-xs text-slate-500 dark:text-slate-400 text-center"
                >
                    <div class="font-medium text-slate-700 dark:text-slate-300">{{ props.employee.location.name }}</div>
                    <div>{{ props.employee.location.full_address }}</div>
                </div>

                <!-- Company footer -->
                <div
                    v-if="props.company.name || props.company.vat_id"
                    class="border-t border-slate-100 dark:border-slate-800 px-6 py-4 text-center text-xs text-slate-400"
                >
                    <div v-if="props.company.name">{{ props.company.name }}</div>
                    <div v-if="props.company.vat_id">NIP: {{ props.company.vat_id }}</div>
                </div>
            </div>

            <p class="mt-6 text-center text-xs text-slate-400">Powered by QrMe</p>
        </div>
    </div>
</template>
