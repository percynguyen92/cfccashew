<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
    Pagination,
    PaginationList,
    PaginationListItem,
} from '@/components/ui/pagination';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useBreadcrumbs } from '@/composables/useBreadcrumbs';
import { useFiltering } from '@/composables/useFiltering';
import {
    usePagination,
    type PaginationData,
} from '@/composables/usePagination';
import AppLayout from '@/layouts/AppLayout.vue';
import * as bills from '@/routes/bills';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowDown, ArrowUp, ArrowUpDown, Plus, Search } from 'lucide-vue-next';

interface Bill {
    id: number;
    slug: string;
    bill_number: string | null;
    seller: string | null;
    buyer: string | null;
    containers_count: number;
    final_samples_count: number;
    average_outurn: number | null;
    created_at: string;
    updated_at: string;
}

interface Props {
    bills: PaginationData & { data: Bill[] };
    filters: {
        search: string;
        sort_by: string;
        sort_direction: 'asc' | 'desc';
    };
}

const props = defineProps<Props>();

const { breadcrumbs } = useBreadcrumbs();
const { goToPage, getPaginationInfo } = usePagination();
const { filters, isLoading, sortBy } = useFiltering(props.filters);

const paginationInfo = getPaginationInfo(props.bills);

const getSortIcon = (column: string) => {
    if (filters.value.sort_by !== column) return ArrowUpDown;
    return filters.value.sort_direction === 'asc' ? ArrowUp : ArrowDown;
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const handleSearch = (event: Event) => {
    const target = event.target as HTMLInputElement;
    filters.value.search = target.value;
};
</script>

<template>

    <Head title="Bills" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Bills</h1>
                    <p class="text-sm text-muted-foreground">
                        Manage Bills of Lading and their associated containers
                    </p>
                </div>
                <Link :href="bills.create.url()">
                <Button>
                    <Plus class="mr-2 h-4 w-4" />
                    Create New Bill
                </Button>
                </Link>
            </div>

            <!-- Search and Filters -->
            <Card class="p-4">
                <div class="flex items-center gap-4">
                    <div class="relative flex-1 max-w-sm">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        <Input :model-value="filters.search" @input="handleSearch"
                            placeholder="Search by bill number, seller, or buyer..." class="pl-9" />
                    </div>
                    <div class="text-sm text-muted-foreground">
                        {{ paginationInfo.total }} total bills
                    </div>
                </div>
            </Card>

            <!-- Bills Table -->
            <Card class="flex-1">
                <div class="relative">
                    <div v-if="isLoading"
                        class="absolute inset-0 bg-background/50 z-10 flex items-center justify-center">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                    </div>

                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>
                                    <Button variant="ghost" size="sm" @click="sortBy('bill_number')"
                                        class="h-auto p-0 font-medium hover:bg-transparent">
                                        Bill Number
                                        <component :is="getSortIcon('bill_number')" class="ml-2 h-4 w-4" />
                                    </Button>
                                </TableHead>
                                <TableHead>
                                    <Button variant="ghost" size="sm" @click="sortBy('seller')"
                                        class="h-auto p-0 font-medium hover:bg-transparent">
                                        Seller
                                        <component :is="getSortIcon('seller')" class="ml-2 h-4 w-4" />
                                    </Button>
                                </TableHead>
                                <TableHead>
                                    <Button variant="ghost" size="sm" @click="sortBy('buyer')"
                                        class="h-auto p-0 font-medium hover:bg-transparent">
                                        Buyer
                                        <component :is="getSortIcon('buyer')" class="ml-2 h-4 w-4" />
                                    </Button>
                                </TableHead>
                                <TableHead>Containers</TableHead>
                                <TableHead>Final Samples</TableHead>
                                <TableHead>Avg. Outurn</TableHead>
                                <TableHead>
                                    <Button variant="ghost" size="sm" @click="sortBy('created_at')"
                                        class="h-auto p-0 font-medium hover:bg-transparent">
                                        Created
                                        <component :is="getSortIcon('created_at')" class="ml-2 h-4 w-4" />
                                    </Button>
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="bill in props.bills.data" :key="bill.id" class="cursor-pointer" @click="
                                router.visit(
                                    bills.show.url(bill.slug || bill.id),
                                )
                                ">
                                <TableCell class="font-medium">
                                    {{ bill.bill_number || 'N/A' }}
                                </TableCell>
                                <TableCell>
                                    {{ bill.seller || 'N/A' }}
                                </TableCell>
                                <TableCell>
                                    {{ bill.buyer || 'N/A' }}
                                </TableCell>
                                <TableCell>
                                    <Badge variant="secondary">
                                        {{ bill.containers_count }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="bill.final_samples_count >= 3
                                            ? 'default'
                                            : 'destructive'
                                        ">
                                        {{ bill.final_samples_count }}/3
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    <span v-if="bill.average_outurn" class="font-medium">
                                        {{
                                            bill.average_outurn.toFixed(2)
                                        }}
                                        lbs/80kg
                                    </span>
                                    <span v-else class="text-muted-foreground">N/A</span>
                                </TableCell>
                                <TableCell class="text-muted-foreground">
                                    {{ formatDate(bill.created_at) }}
                                </TableCell>
                            </TableRow>

                            <!-- Empty State -->
                            <TableRow v-if="props.bills.data.length === 0">
                                <TableCell :colspan="7" class="py-8 text-center">
                                    <div class="text-muted-foreground">
                                        <p class="text-lg font-medium">
                                            No bills found
                                        </p>
                                        <p class="text-sm">
                                            <template v-if="filters.search">
                                                Try adjusting your search
                                                criteria
                                            </template>
                                            <template v-else>
                                                Get started by creating your
                                                first bill
                                            </template>
                                        </p>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </Card>

            <!-- Pagination -->
            <div v-if="props.bills.data.length > 0" class="flex items-center justify-between">
                <div class="text-sm text-muted-foreground">
                    Showing {{ paginationInfo.from }} to
                    {{ paginationInfo.to }} of
                    {{ paginationInfo.total }} results
                </div>

                <Pagination>
                    <PaginationList>
                        <PaginationListItem>
                            <Button variant="outline" size="sm" :disabled="!paginationInfo.hasPrevPage"
                                @click="goToPage(props.bills.prev_page_url)">
                                Previous
                            </Button>
                        </PaginationListItem>

                        <PaginationListItem v-for="link in props.bills.links.slice(1, -1)" :key="link.label">
                            <Button :variant="link.active ? 'default' : 'outline'" size="sm" :disabled="!link.url"
                                @click="goToPage(link.url)" v-html="link.label" />
                        </PaginationListItem>

                        <PaginationListItem>
                            <Button variant="outline" size="sm" :disabled="!paginationInfo.hasNextPage"
                                @click="goToPage(props.bills.next_page_url)">
                                Next
                            </Button>
                        </PaginationListItem>
                    </PaginationList>
                </Pagination>
            </div>
        </div>
    </AppLayout>
</template>
