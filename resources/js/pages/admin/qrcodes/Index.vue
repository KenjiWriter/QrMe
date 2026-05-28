<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';

type QrItem = {
    id: number;
    short_id: string;
    name: string;
    url: string;
    qr_color: string;
    qr_eye_shape: string;
    logo_url: string | null;
    qr_code_url: string | null;
    scan_url: string;
    scan_count: number;
};

const props = defineProps<{ items: QrItem[] }>();
const { t } = useI18n();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: '/admin/employees' },
            { title: 'QR Codes', href: '/admin/qrcodes' },
        ],
    },
});

function destroy(item: QrItem) {
    if (!confirm(t('qrcodes.confirm_delete', { name: item.name }))) return;
    router.delete(`/admin/qrcodes/${item.short_id}`, { preserveScroll: true });
}
</script>

<template>
    <Head :title="t('qrcodes.title')" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <Heading
                variant="small"
                :title="t('qrcodes.title')"
                :description="t('qrcodes.description')"
            />
            <Link href="/admin/qrcodes/create">
                <Button>{{ t('qrcodes.add') }}</Button>
            </Link>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
                v-for="item in props.items"
                :key="item.id"
                class="rounded-xl border border-sidebar-border/70 p-4 flex flex-col gap-3"
            >
                <!-- Header: colour dot + name -->
                <div class="flex items-center gap-2">
                    <span
                        class="h-4 w-4 rounded-full border flex-shrink-0"
                        :style="{ backgroundColor: item.qr_color }"
                    />
                    <div class="min-w-0">
                        <div class="font-medium truncate">{{ item.name }}</div>
                        <a
                            :href="item.url"
                            target="_blank"
                            class="text-xs text-muted-foreground hover:underline truncate block"
                        >{{ item.url }}</a>
                    </div>
                </div>

                <!-- QR preview + meta -->
                <div v-if="item.qr_code_url" class="flex items-start gap-3">
                    <img :src="item.qr_code_url" class="h-20 w-20 flex-shrink-0" alt="QR code" />
                    <div class="flex flex-col gap-1 text-xs">
                        <a :href="item.qr_code_url" download class="text-muted-foreground hover:underline">
                            {{ t('qrcodes.download_qr') }}
                        </a>
                        <span class="text-muted-foreground flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            {{ t('qrcodes.scans', { count: item.scan_count }) }}
                        </span>
                        <span class="text-muted-foreground capitalize">
                            {{ t('qr.eye_shape_label') }}: {{ t(`qr.eye_${item.qr_eye_shape}`) }}
                        </span>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <Link :href="`/admin/qrcodes/${item.short_id}/edit`">
                        <Button variant="outline" size="sm">{{ t('common.edit') }}</Button>
                    </Link>
                    <Button variant="destructive" size="sm" @click="destroy(item)">{{ t('common.delete') }}</Button>
                </div>
            </div>

            <div
                v-if="!props.items.length"
                class="col-span-full rounded-xl border border-dashed p-8 text-center text-muted-foreground"
            >
                {{ t('qrcodes.empty') }}
            </div>
        </div>
    </div>
</template>
