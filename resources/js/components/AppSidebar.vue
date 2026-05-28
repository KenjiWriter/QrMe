<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Building2, QrCode, Settings2, Users } from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import LocaleSwitcher from '@/components/LocaleSwitcher.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import type { NavItem } from '@/types';

const { t } = useI18n();

const mainNavItems = computed<NavItem[]>(() => [
    {
        title: t('nav.employees'),
        href: '/admin/employees',
        icon: Users,
    },
    {
        title: t('nav.locations'),
        href: '/admin/locations',
        icon: Building2,
    },
    {
        title: t('nav.qrcodes'),
        href: '/admin/qrcodes',
        icon: QrCode,
    },
    {
        title: t('nav.settings'),
        href: '/admin/settings',
        icon: Settings2,
    },
]);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/admin/employees">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <LocaleSwitcher />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
