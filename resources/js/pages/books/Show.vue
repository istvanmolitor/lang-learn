<script setup lang="ts">
import { index, show } from '@/actions/App/Http/Controllers/BookController';
import { Head, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

type SentenceItem = {
    id: number;
    text: string;
    translation: string | null;
};

type ParagraphItem = {
    id: number;
    sentences: SentenceItem[];
};

const props = defineProps<{
    book: {
        id: number;
        title: string;
        language: string;
    };
    paragraphs: ParagraphItem[];
    pagination: {
        currentPage: number;
        lastPage: number;
    };
}>();

const revealedSentenceIds = ref(new Set<number>());

watch(
    () => props.pagination.currentPage,
    () => revealedSentenceIds.value.clear(),
);

function toggleSentence(sentenceId: number) {
    if (revealedSentenceIds.value.has(sentenceId)) {
        revealedSentenceIds.value.delete(sentenceId);
    } else {
        revealedSentenceIds.value.add(sentenceId);
    }
}

function pageHref(page: number) {
    return show(props.book.id, { query: { page } });
}
</script>

<template>
    <Head :title="book.title" />
    <div
        class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a]"
    >
        <div class="w-full max-w-2xl">
            <div class="mb-4 flex items-center justify-between">
                <Link
                    :href="index()"
                    class="text-sm text-[#706f6c] underline underline-offset-4 hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                >
                    ← Vissza a könyvekhez
                </Link>
                <span
                    class="text-xs text-[#706f6c] uppercase dark:text-[#A1A09A]"
                >
                    {{ book.language }}
                </span>
            </div>

            <div
                class="w-full rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <h1 class="mb-6 font-medium">{{ book.title }}</h1>

                <p
                    v-if="paragraphs.length === 0"
                    class="text-sm text-[#706f6c] dark:text-[#A1A09A]"
                >
                    Ehhez a könyvhöz még nincs szöveg.
                </p>

                <div v-else class="flex flex-col gap-4">
                    <p
                        v-for="paragraph in paragraphs"
                        :key="paragraph.id"
                        class="leading-relaxed"
                    >
                        <template
                            v-for="sentence in paragraph.sentences"
                            :key="sentence.id"
                        >
                            <button
                                type="button"
                                class="cursor-pointer rounded-sm text-left underline-offset-4 hover:bg-[#f5f5f4] hover:underline dark:hover:bg-[#1f1f1d]"
                                @click="toggleSentence(sentence.id)"
                            >
                                {{ sentence.text }}
                            </button>
                            <span
                                v-if="revealedSentenceIds.has(sentence.id)"
                                class="mx-1 text-sm text-[#706f6c] italic dark:text-[#A1A09A]"
                            >
                                ({{ sentence.translation ?? 'Nincs fordítás.' }})
                            </span>
                            {{ ' ' }}
                        </template>
                    </p>
                </div>

                <div
                    class="mt-6 flex items-center justify-between border-t border-[#e3e3e0] pt-4 dark:border-[#3E3E3A]"
                >
                    <Link
                        v-if="pagination.currentPage > 1"
                        :href="pageHref(pagination.currentPage - 1)"
                        class="rounded-sm border border-[#e3e3e0] px-4 py-1.5 text-sm hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#EDEDEC]"
                    >
                        ← Előző oldal
                    </Link>
                    <span v-else />

                    <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        {{ pagination.currentPage }} / {{ pagination.lastPage }}
                    </span>

                    <Link
                        v-if="pagination.currentPage < pagination.lastPage"
                        :href="pageHref(pagination.currentPage + 1)"
                        class="rounded-sm border border-[#e3e3e0] px-4 py-1.5 text-sm hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#EDEDEC]"
                    >
                        Következő oldal →
                    </Link>
                    <span v-else />
                </div>
            </div>
        </div>
    </div>
</template>
