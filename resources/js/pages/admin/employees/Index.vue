<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';

type Employee = {
    id: number;
    uuid: string;
    full_name: string;
    job_title: string;
    email: string;
    photo_url: string | null;
    qr_code_url: string | null;
    public_url: string;
    location: { name: string } | null;
};

const props = defineProps<{ employees: Employee[] }>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Admin', href: '/admin/employees' },
            { title: 'Employees', href: '/admin/employees' },
        ],
    },
});

function destroy(emp: Employee) {
    if (!confirm(`Delete ${emp.full_name}?`)) return;
    router.delete(`/admin/employees/${emp.id}`, { preserveScroll: true });
}
</script>

<template>
    <Head title="Employees" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <Heading
                variant="small"
                title="Employees"
                description="Digital business cards. QR codes are generated automatically."
            />
            <Link href="/admin/employees/create">
                <Button>Add employee</Button>
            </Link>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
                v-for="emp in props.employees"
                :key="emp.id"
                class="rounded-xl border border-sidebar-border/70 p-4 flex flex-col gap-3"
            >
                <div class="flex items-center gap-3">
                    <img
                        v-if="emp.photo_url"
                        :src="emp.photo_url"
                        class="h-14 w-14 rounded-full object-cover"
                    />
                    <div
                        v-else
                        class="h-14 w-14 rounded-full bg-muted flex items-center justify-center text-lg font-medium"
                    >
                        {{ emp.full_name.charAt(0) }}
                    </div>
                    <div class="min-w-0">
                        <div class="font-medium truncate">{{ emp.full_name }}</div>
                        <div class="text-xs text-muted-foreground truncate">{{ emp.job_title }}</div>
                        <div class="text-xs text-muted-foreground truncate">{{ emp.email }}</div>
                    </div>
                </div>

                <div v-if="emp.qr_code_url" class="flex items-center gap-3">
                    <img :src="emp.qr_code_url" class="h-20 w-20" alt="QR code" />
                    <div class="flex flex-col gap-1 text-xs">
                        <a :href="emp.public_url" target="_blank" class="text-blue-600 hover:underline break-all">
                            {{ emp.public_url }}
                        </a>
                        <a :href="emp.qr_code_url" download class="text-muted-foreground hover:underline">
                            Download QR
                        </a>
                    </div>
                </div>

                <div class="flex justify-end gap-2">
                    <Link :href="`/admin/employees/${emp.id}/edit`">
                        <Button variant="outline" size="sm">Edit</Button>
                    </Link>
                    <Button variant="destructive" size="sm" @click="destroy(emp)">Delete</Button>
                </div>
            </div>

            <div
                v-if="!props.employees.length"
                class="col-span-full rounded-xl border border-dashed p-8 text-center text-muted-foreground"
            >
                No employees yet.
            </div>
        </div>
    </div>
</template>
