<script setup lang="ts">
import { index } from '@/actions/App/Http/Controllers/ArticleController';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

type SentenceItem = {
    id: number;
    text: string;
    translation: string | null;
};

type ParagraphItem = {
    id: number;
    sentences: SentenceItem[];
};

defineProps<{
    article: {
        id: number;
        title: string;
        language: string;
        paragraphs: ParagraphItem[];
    };
}>();

const revealedSentenceIds = ref(new Set<number>());

function toggleSentence(sentenceId: number) {
    if (revealedSentenceIds.value.has(sentenceId)) {
        revealedSentenceIds.value.delete(sentenceId);
    } else {
        revealedSentenceIds.value.add(sentenceId);
    }
}
</script>

<template>
    <Head :title="article.title" />
    <div
        class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a]"
    >
        <div class="w-full max-w-2xl">
            <div class="mb-4 flex items-center justify-between">
                <Link
                    :href="index()"
                    class="text-sm text-[#706f6c] underline underline-offset-4 hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                >
                    ← Vissza a cikkekhez
                </Link>
                <span
                    class="text-xs text-[#706f6c] uppercase dark:text-[#A1A09A]"
                >
                    {{ article.language }}
                </span>
            </div>

            <div
                class="w-full rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <h1 class="mb-6 font-medium">{{ article.title }}</h1>

                <div class="flex flex-col gap-4">
                    <p
                        v-for="paragraph in article.paragraphs"
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
            </div>
        </div>
    </div>
</template>
