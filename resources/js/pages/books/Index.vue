<script setup lang="ts">
import {
    create,
    show,
} from '@/actions/App/Http/Controllers/BookController';
import { Head, Link } from '@inertiajs/vue3';

type BookListItem = {
    id: number;
    title: string;
    language: string;
};

defineProps<{
    books: BookListItem[];
    status?: string | null;
}>();
</script>

<template>
    <Head title="Könyvek" />
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
            </div>

            <div
                class="w-full rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <div class="mb-6 flex items-center justify-between">
                    <h1 class="font-medium">Könyvek</h1>
                    <Link
                        :href="create()"
                        class="inline-block rounded-sm border border-[#e3e3e0] px-5 py-1.5 text-sm leading-normal hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#EDEDEC]"
                    >
                        Könyv feltöltése
                    </Link>
                </div>

                <p
                    v-if="status"
                    class="mb-6 rounded-sm border border-[#e3e3e0] px-3 py-2 text-sm dark:border-[#3E3E3A]"
                >
                    {{ status }}
                </p>

                <p
                    v-if="books.length === 0"
                    class="text-sm text-[#706f6c] dark:text-[#A1A09A]"
                >
                    Még nincs könyv.
                </p>

                <ul v-else class="flex flex-col gap-4">
                    <li
                        v-for="book in books"
                        :key="book.id"
                        class="flex items-center justify-between gap-4 border-b border-[#e3e3e0] pb-4 last:border-0 last:pb-0 dark:border-[#3E3E3A]"
                    >
                        <div class="flex flex-col gap-1">
                            <Link
                                :href="show(book.id)"
                                class="font-medium underline-offset-4 hover:underline"
                            >
                                {{ book.title }}
                            </Link>
                            <span
                                class="text-xs text-[#706f6c] uppercase dark:text-[#A1A09A]"
                            >
                                {{ book.language }}
                            </span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
