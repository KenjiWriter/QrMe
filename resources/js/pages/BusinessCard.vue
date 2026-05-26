<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import {
    Briefcase,
    ChevronRight,
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
        { url: props.employee.facebook_url, icon: Facebook, label: 'Facebook', bg: '#1877F2' },
        { url: props.employee.instagram_url, icon: Instagram, label: 'Instagram', bg: '#E1306C' },
        { url: props.employee.linkedin_url, icon: Linkedin, label: 'LinkedIn', bg: '#0077B5' },
        { url: props.employee.youtube_url, icon: Youtube, label: 'YouTube', bg: '#FF0000' },
    ].filter((s) => !!s.url),
);
</script>

<template>
    <Head :title="props.employee.full_name" />

    <div class="min-h-svh bg-white text-slate-900">
        <!-- Orange hero with wave bottom -->
        <section class="relative bg-orange-500 pb-16">
            <!-- Locale switcher -->
            <div class="absolute top-3 right-3 z-10">
                <LocaleSwitcher variant="floating" />
            </div>

            <!-- Avatar + name + title -->
            <div class="flex flex-col items-center pt-10 pb-6 px-6">
                <img
                    v-if="props.employee.photo_url"
                    :src="props.employee.photo_url"
                    :alt="props.employee.full_name"
                    class="h-36 w-36 rounded-full object-cover border-4 border-white shadow-lg"
                />
                <div
                    v-else
                    class="h-36 w-36 rounded-full bg-white/30 border-4 border-white shadow-lg flex items-center justify-center text-4xl font-bold text-white"
                >
                    {{ initials }}
                </div>
                <h1 class="mt-4 text-2xl font-bold text-slate-900 text-center">
                    {{ props.employee.full_name }}
                </h1>
                <p
                    v-if="props.employee.job_title"
                    class="mt-1 text-sm text-slate-700 text-center px-6 leading-relaxed"
                >
                    {{ props.employee.job_title }}
                </p>
            </div>

            <!-- Wave: white fill creates smooth orange → white transition -->
            <div class="absolute bottom-0 left-0 right-0 leading-none">
                <svg
                    viewBox="0 0 1440 80"
                    preserveAspectRatio="none"
                    xmlns="http://www.w3.org/2000/svg"
                    class="w-full h-16"
                >
                    <path fill="white" d="M0,60 C360,0 1080,0 1440,60 L1440,80 L0,80 Z" />
                </svg>
            </div>

            <!-- Quick-action icon buttons sitting on the wave -->
            <div class="absolute -bottom-7 inset-x-0 flex justify-center gap-5 z-10">
                <a
                    v-if="props.employee.phone"
                    :href="`tel:${props.employee.phone}`"
                    :aria-label="t('card.phone')"
                    class="h-14 w-14 rounded-full bg-white shadow-md flex items-center justify-center text-slate-600 hover:bg-slate-50 transition"
                >
                    <Phone class="h-5 w-5" />
                </a>
                <a
                    :href="`mailto:${props.employee.email}`"
                    :aria-label="t('card.email')"
                    class="h-14 w-14 rounded-full bg-white shadow-md flex items-center justify-center text-slate-600 hover:bg-slate-50 transition"
                >
                    <Mail class="h-5 w-5" />
                </a>
                <a
                    v-if="props.employee.location"
                    :href="props.employee.location.maps_url"
                    target="_blank"
                    rel="noopener"
                    :aria-label="t('card.maps')"
                    class="h-14 w-14 rounded-full bg-white shadow-md flex items-center justify-center text-slate-600 hover:bg-slate-50 transition"
                >
                    <MapPin class="h-5 w-5" />
                </a>
            </div>
        </section>

        <!-- Main body -->
        <main class="pt-14 pb-10 px-5 max-w-lg mx-auto flex flex-col gap-5">
            <!-- Add contact CTA -->
            <a
                :href="props.vcardUrl"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white py-4 text-base font-semibold shadow transition active:scale-[0.99]"
            >
                <UserPlus class="h-5 w-5" />
                {{ t('card.add_contact') }}
            </a>

            <!-- Company name + VAT -->
            <div
                v-if="props.company.name || props.company.vat_id"
                class="text-center text-sm text-slate-500 leading-relaxed"
            >
                <div v-if="props.company.name">{{ props.company.name }}</div>
                <div v-if="props.company.vat_id">VAT EU: {{ props.company.vat_id }}</div>
            </div>

            <!-- Contact details list -->
            <div class="divide-y divide-slate-100 rounded-2xl border border-slate-100 overflow-hidden">
                <a
                    v-if="props.employee.phone"
                    :href="`tel:${props.employee.phone}`"
                    class="flex items-center gap-4 px-4 py-4 hover:bg-slate-50 transition"
                >
                    <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center flex-none">
                        <Phone class="h-4 w-4 text-slate-500" />
                    </div>
                    <div>
                        <div class="text-xs text-slate-500">{{ t('card.phone') }}</div>
                        <div class="text-sm font-medium text-slate-900">{{ props.employee.phone }}</div>
                    </div>
                </a>

                <a
                    :href="`mailto:${props.employee.email}`"
                    class="flex items-center gap-4 px-4 py-4 hover:bg-slate-50 transition"
                >
                    <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center flex-none">
                        <Mail class="h-4 w-4 text-slate-500" />
                    </div>
                    <div>
                        <div class="text-xs text-slate-500">{{ t('card.email') }}</div>
                        <div class="text-sm font-medium text-slate-900">{{ props.employee.email }}</div>
                    </div>
                </a>

                <div v-if="props.employee.location" class="flex items-start gap-4 px-4 py-4">
                    <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center flex-none mt-0.5">
                        <MapPin class="h-4 w-4 text-slate-500" />
                    </div>
                    <div>
                        <div class="text-xs text-slate-500">{{ t('card.location_label') }}</div>
                        <div class="text-sm font-medium text-slate-900">
                            {{ props.employee.location.full_address }}
                        </div>
                        <a
                            :href="props.employee.location.maps_url"
                            target="_blank"
                            rel="noopener"
                            class="mt-2 inline-block text-xs px-3 py-1 rounded-full bg-teal-50 text-teal-700 font-medium hover:bg-teal-100 transition"
                        >
                            {{ t('card.show_on_map') }}
                        </a>
                    </div>
                </div>

                <div v-if="props.company.name" class="flex items-center gap-4 px-4 py-4">
                    <div class="h-10 w-10 rounded-full bg-slate-100 flex items-center justify-center flex-none">
                        <Briefcase class="h-4 w-4 text-slate-500" />
                    </div>
                    <div>
                        <div class="text-xs text-slate-500">{{ props.company.name }}</div>
                        <div class="text-sm font-medium text-slate-900">{{ props.employee.job_title }}</div>
                    </div>
                </div>
            </div>

            <!-- Bio -->
            <p
                v-if="props.employee.bio"
                class="text-sm leading-relaxed text-slate-600 whitespace-pre-line"
            >
                {{ props.employee.bio }}
            </p>

            <!-- Social links -->
            <div v-if="socials.length">
                <h2 class="text-base font-semibold text-slate-900 mb-3">
                    {{ t('card.find_me_on') }}
                </h2>
                <div class="divide-y divide-slate-100 rounded-2xl border border-slate-100 overflow-hidden">
                    <a
                        v-for="s in socials"
                        :key="s.label"
                        :href="s.url!"
                        target="_blank"
                        rel="noopener"
                        class="flex items-center gap-4 px-4 py-4 hover:bg-slate-50 transition"
                    >
                        <div
                            class="h-10 w-10 rounded-full flex items-center justify-center flex-none"
                            :style="{ background: s.bg }"
                        >
                            <component :is="s.icon" class="h-5 w-5 text-white" />
                        </div>
                        <span class="text-sm font-medium text-slate-900 flex-1">{{ s.label }}</span>
                        <ChevronRight class="h-4 w-4 text-slate-400 flex-none" />
                    </a>
                </div>
            </div>

            <p class="text-center text-xs text-slate-400 pt-2">
                {{ t('card.powered_by') }}
            </p>
        </main>
    </div>
</template>
