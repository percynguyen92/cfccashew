import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

export type NavigationSection =
    | 'dashboard'
    | 'bills'
    | 'containers'
    | 'cutting-tests';

export interface NavigationState {
    currentSection: NavigationSection;
    sidebarCollapsed: boolean;
}

const sidebarCollapsed = ref(false);

export function useNavigation() {
    const page = usePage();

    const currentSection = computed<NavigationSection>(() => {
        const url = page.url;

        if (url.startsWith('/bills')) {
            return 'bills';
        } else if (url.startsWith('/containers')) {
            return 'containers';
        } else if (url.startsWith('/cutting-tests')) {
            return 'cutting-tests';
        } else {
            return 'dashboard';
        }
    });

    const toggleSidebar = () => {
        sidebarCollapsed.value = !sidebarCollapsed.value;
    };

    const setSidebarCollapsed = (collapsed: boolean) => {
        sidebarCollapsed.value = collapsed;
    };

    return {
        currentSection,
        sidebarCollapsed: computed(() => sidebarCollapsed.value),
        toggleSidebar,
        setSidebarCollapsed,
    };
}
