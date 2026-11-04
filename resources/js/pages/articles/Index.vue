<script setup lang="ts">
import { show } from '@/actions/App/Http/Controllers/ArticleController';
import { Head, Link } from '@inertiajs/vue3';

type ArticleListItem = {
    id: number;
    title: string;
    language: string;
};

defineProps<{
    articles: ArticleListItem[];
}>();
</script>

<template>
    <Head title="Cikkek" />
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
                <h1 class="mb-6 font-medium">Cikkek</h1>

                <p
                    v-if="articles.length === 0"
                    class="text-sm text-[#706f6c] dark:text-[#A1A09A]"
                >
                    Még nincs cikk.
                </p>

                <ul v-else class="flex flex-col gap-4">
                    <li
                        v-for="article in articles"
                        :key="article.id"
                        class="flex items-center justify-between gap-4 border-b border-[#e3e3e0] pb-4 last:border-0 last:pb-0 dark:border-[#3E3E3A]"
                    >
                        <div class="flex flex-col gap-1">
                            <Link
                                :href="show(article.id)"
                                class="font-medium underline-offset-4 hover:underline"
                            >
                                {{ article.title }}
                            </Link>
                            <span
                                class="text-xs text-[#706f6c] uppercase dark:text-[#A1A09A]"
                            >
                                {{ article.language }}
                            </span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
