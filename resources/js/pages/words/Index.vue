<script setup lang="ts">
import { index } from '@/actions/App/Http/Controllers/WordController';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

type WordListItem = {
    id: number;
    word: string;
    sentences_count: number;
    cards_count: number;
};

type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

type Paginated<T> = {
    data: T[];
    links: PaginationLink[];
};

const props = defineProps<{
    words: Paginated<WordListItem>;
    search: string;
}>();

const search = ref(props.search);

let debounceTimeout: ReturnType<typeof setTimeout> | undefined;

watch(search, (value) => {
    clearTimeout(debounceTimeout);

    debounceTimeout = setTimeout(() => {
        router.get(
            index.url(),
            { search: value || undefined },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }, 300);
});
</script>

<template>
    <Head title="Szavak" />
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
                <h1 class="mb-6 font-medium">Szavak</h1>

                <input
                    v-model="search"
                    type="search"
                    placeholder="Keresés..."
                    class="mb-6 w-full rounded-sm border border-[#e3e3e0] bg-transparent px-3 py-1.5 text-sm outline-none focus:border-[#1b1b18] dark:border-[#3E3E3A] dark:focus:border-[#EDEDEC]"
                />

                <p
                    v-if="words.data.length === 0"
                    class="text-sm text-[#706f6c] dark:text-[#A1A09A]"
                >
                    Nincs a keresésnek megfelelő szó.
                </p>

                <table v-else class="w-full text-sm">
                    <thead>
                        <tr
                            class="border-b border-[#e3e3e0] text-left text-[#706f6c] dark:border-[#3E3E3A] dark:text-[#A1A09A]"
                        >
                            <th class="py-2 font-medium">Szó</th>
                            <th class="py-2 text-right font-medium">
                                Mondatok
                            </th>
                            <th class="py-2 text-right font-medium">
                                Kártyák
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="word in words.data"
                            :key="word.id"
                            class="border-b border-[#e3e3e0] last:border-0 dark:border-[#3E3E3A]"
                        >
                            <td class="py-2">{{ word.word }}</td>
                            <td class="py-2 text-right">
                                {{ word.sentences_count }}
                            </td>
                            <td class="py-2 text-right">
                                {{ word.cards_count }}
                            </td>
                        </tr>
                    </tbody>
                </table>

                <nav
                    v-if="words.links.length > 3"
                    class="mt-6 flex flex-wrap items-center gap-1"
                >
                    <template v-for="(link, index) in words.links" :key="index">
                        <span
                            v-if="link.url === null"
                            class="rounded-sm px-3 py-1 text-sm text-[#b4b3af] dark:text-[#5a5a57]"
                            v-html="link.label"
                        />
                        <Link
                            v-else
                            :href="link.url"
                            preserve-scroll
                            class="rounded-sm px-3 py-1 text-sm hover:bg-[#f5f5f4] dark:hover:bg-[#1f1f1d]"
                            :class="
                                link.active
                                    ? 'bg-[#1b1b18] text-white dark:bg-[#eeeeec] dark:text-[#1C1C1A]'
                                    : 'text-[#706f6c] dark:text-[#A1A09A]'
                            "
                            v-html="link.label"
                        />
                    </template>
                </nav>
            </div>
        </div>
    </div>
</template>
