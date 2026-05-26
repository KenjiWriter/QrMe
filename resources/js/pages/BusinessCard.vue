<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import {
    Facebook,
    Instagram,
    Linkedin,
    Mail,
    MapPin,
    Phone,
    UserPlus,
    Youtube,
} from 'lucide-vue-next';
import LocaleSwitcher from '@/components/LocaleSwitcher.vue';

type Employee = {
    short_id: string;
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

const { t } = useI18n();

const initials = computed(() =>
    (props.employee.first_name.charAt(0) + props.employee.last_name.charAt(0)).toUpperCase(),
);

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

    <div class="min-h-svh w-full bg-white text-slate-900">
        <!-- Orange header banner -->
        <header class="relative w-full bg-orange-500 h-44 sm:h-56">
            <div class="absolute top-3 right-3 z-10">
                <LocaleSwitcher variant="floating" />
            </div>

            <!-- Avatar overlapping the bottom edge -->
            <div class="absolute left-1/2 -bottom-14 -translate-x-1/2">
                <img
                    v-if="props.employee.photo_url"
                    :src="props.employee.photo_url"
                    :alt="props.employee.full_name"
                    class="h-28 w-28 sm:h-32 sm:w-32 rounded-full object-cover border-4 border-white shadow-lg"
                />
                <div
                    v-else
                    class="h-28 w-28 sm:h-32 sm:w-32 rounded-full bg-orange-100 border-4 border-white shadow-lg flex items-center justify-center text-3xl font-semibold text-orange-600"
                >
                    {{ initials }}
                </div>
            </div>
        </header>

        <!-- Body -->
        <main class="px-5 sm:px-8 pt-20 pb-10 flex flex-col gap-6 max-w-2xl mx-auto w-full">
            <!-- Name + title -->
            <section class="text-center">
                <h1 class="text-2xl font-semibold tracking-tight">
                    {{ props.employee.full_name }}
                </h1>
                <p v-if="props.employee.job_title" class="mt-1 text-base text-slate-600">
                    {{ props.employee.job_title }}
                </p>
                <p v-if="props.company.name" class="mt-1 text-sm font-medium text-orange-600">
                    {{ props.company.name }}
                </p>
            </section>

            <!-- Bio -->
            <p
                v-if="props.employee.bio"
                class="text-sm leading-relaxed text-slate-700 whitespace-pre-line text-center"
            >
                {{ props.employee.bio }}
            </p>

            <!-- Primary action (Add contact) -->
            <a
                :href="props.vcardUrl"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white px-5 py-4 text-base font-semibold shadow-md active:scale-[0.99] transition"
            >
                <UserPlus class="h-5 w-5" />
                {{ t('card.add_contact') }}
            </a>

            <!-- Secondary actions grid -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <a
                    :href="`mailto:${props.employee.email}`"
                    class="flex items-center justify-center gap-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white px-4 py-3.5 text-sm font-medium active:scale-[0.99] transition"
                >
                    <Mail class="h-4 w-4" />
                    {{ t('card.email') }}
                </a>
                <a
                    v-if="props.employee.phone"
                    :href="`tel:${props.employee.phone}`"
                    class="flex items-center justify-center gap-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white px-4 py-3.5 text-sm font-medium active:scale-[0.99] transition"
                >
                    <Phone class="h-4 w-4" />
                    {{ t('card.phone') }}
                </a>
                <a
                    v-if="props.employee.location"
                    :href="props.employee.location.maps_url"
                    target="_blank"
                    rel="noopener"
                    class="flex items-center justify-center gap-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-900 px-4 py-3.5 text-sm font-medium active:scale-[0.99] transition"
                >
                    <MapPin class="h-4 w-4" />
                    {{ t('card.maps') }}
                </a>
            </div>

            <!-- Social links -->
            <div v-if="socials.length" class="flex justify-center gap-4 pt-2">
                <a
                    v-for="s in socials"
                    :key="s.label"
                    :href="s.url!"
                    target="_blank"
                    rel="noopener"
                    :aria-label="s.label"
                    class="h-12 w-12 rounded-full bg-orange-50 hover:bg-orange-100 flex items-center justify-center text-orange-600 active:scale-95 transition"
                >
                    <component :is="s.icon" class="h-5 w-5" />
                </a>
            </div>

            <!-- Location -->
            <section
                v-if="props.employee.location"
                class="mt-2 rounded-xl border border-slate-200 px-5 py-4 text-sm"
            >
                <div class="flex items-start gap-3">
                    <MapPin class="h-5 w-5 text-orange-500 flex-none mt-0.5" />
                    <div>
                        <div class="font-semibold text-slate-900">
                            {{ props.employee.location.name }}
                        </div>
                        <div class="text-slate-600 mt-0.5">
                            {{ props.employee.location.full_address }}
                        </div>
                    </div>
                </div>
            </section>

            <!-- Company footer -->
            <footer
                v-if="props.company.name || props.company.vat_id"
                class="mt-2 border-t border-slate-100 pt-4 text-center text-xs text-slate-500"
            >
                <div v-if="props.company.name" class="font-medium text-slate-700">
                    {{ props.company.name }}
                </div>
                <div v-if="props.company.vat_id" class="mt-0.5">
                    {{ t('card.vat_id') }}: {{ props.company.vat_id }}
                </div>
            </footer>

            <p class="text-center text-xs text-slate-400 mt-2">
                {{ t('card.powered_by') }}
            </p>
        </main>
    </div>
</template>
