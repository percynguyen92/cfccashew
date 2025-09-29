<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import bills from '@/routes/bills';
import containers from '@/routes/containers';
import cuttingTests from '@/routes/cutting-tests';
import { dashboard } from '@/routes/index';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { FileText, LayoutGrid, Package, Scissors } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import DarkModeToggle from './DarkModeToggle.vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const mainNavItems = computed<NavItem[]>(() => [
    {
        title: t('navigation.dashboard'),
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: t('navigation.bills'),
        href: bills.index(),
        icon: FileText,
    },
    {
        title: t('navigation.containers'),
        href: containers.index(),
        icon: Package,
    },
    {
        title: t('navigation.cuttingTests'),
        href: cuttingTests.index(),
        icon: Scissors,
    },
]);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <div class="flex items-center justify-between gap-2">
                <SidebarMenu class="flex-1 min-w-0">
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" as-child>
                            <Link :href="dashboard()">
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
                <DarkModeToggle />
            </div>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
