<script setup lang="ts">
import {
    create,
    destroy,
    edit,
} from '@/actions/App/Http/Controllers/CardGroupController';
import { Head, Link, router } from '@inertiajs/vue3';

type CardGroupListItem = {
    id: number;
    name: string;
    cards_count: number;
};

defineProps<{
    groups: CardGroupListItem[];
}>();

function remove(groupId: number) {
    if (window.confirm('Biztosan törlöd ezt a csoportot?')) {
        router.delete(destroy.url(groupId));
    }
}
</script>

<template>
    <Head title="Kártyacsoportok" />
    <div
        class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a]"
    >
        <div class="w-full max-w-2xl">
            <div class="mb-4 flex items-center justify-between">
                <Link
                    href="/dashboard"
                    class="text-sm text-[#706f6c] underline underline-offset-4 hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                >
                    ← Vissza
                </Link>
                <Link
                    :href="create()"
                    class="text-sm text-[#706f6c] underline underline-offset-4 hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                >
                    + Új csoport
                </Link>
            </div>

            <div
                class="w-full rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <h1 class="mb-6 font-medium">Kártyacsoportok</h1>

                <p
                    v-if="groups.length === 0"
                    class="text-sm text-[#706f6c] dark:text-[#A1A09A]"
                >
                    Még nincs csoport.
                </p>

                <ul v-else class="flex flex-col gap-3">
                    <li
                        v-for="group in groups"
                        :key="group.id"
                        class="flex items-center justify-between gap-4 border-b border-[#e3e3e0] pb-3 last:border-0 last:pb-0 dark:border-[#3E3E3A]"
                    >
                        <div class="flex items-center gap-2 text-sm">
                            <span>{{ group.name }}</span>
                            <span class="text-[#706f6c] dark:text-[#A1A09A]"
                                >({{ group.cards_count }} kártya)</span
                            >
                        </div>

                        <div class="flex items-center gap-3 text-sm">
                            <Link
                                :href="edit(group.id)"
                                class="text-[#706f6c] underline underline-offset-4 hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                            >
                                Szerkesztés
                            </Link>
                            <button
                                type="button"
                                class="text-[#f53003] dark:text-[#FF4433]"
                                @click="remove(group.id)"
                            >
                                Törlés
                            </button>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
