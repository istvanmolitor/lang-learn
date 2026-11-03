<script setup lang="ts">
import { store } from '@/actions/App/Http/Controllers/LearnController';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

type LearnCard = {
    id: number;
    front: string | null;
    frontLanguage: string | null;
    back: string | null;
    backLanguage: string | null;
};

const props = defineProps<{
    group: {
        id: number;
        name: string;
    };
    card: LearnCard | null;
}>();

const flipped = ref(false);

watch(
    () => props.card?.id,
    () => {
        flipped.value = false;
    },
);

function flip() {
    flipped.value = !flipped.value;
}

function answer(difficulty: 'forgot' | 'hard' | 'medium' | 'easy') {
    if (!props.card) {
        return;
    }

    router.post(store.url(props.group.id), {
        card_id: props.card.id,
        difficulty,
    });
}
</script>

<template>
    <Head title="Tanulás" />
    <div
        class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a]"
    >
        <div class="w-full max-w-lg">
            <div class="mb-4 flex items-center justify-between">
                <Link
                    href="/card-groups"
                    class="text-sm text-[#706f6c] underline underline-offset-4 hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                >
                    ← Vissza a csoportokhoz
                </Link>
                <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">{{
                    group.name
                }}</span>
            </div>

            <div
                v-if="card"
                class="w-full rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <button
                    type="button"
                    class="flex min-h-48 w-full flex-col items-center justify-center rounded-lg border border-[#e3e3e0] px-6 py-10 text-center dark:border-[#3E3E3A]"
                    @click="flip"
                >
                    <span
                        v-if="
                            (flipped ? card.backLanguage : card.frontLanguage)
                        "
                        class="mb-2 text-xs tracking-wide text-[#706f6c] uppercase dark:text-[#A1A09A]"
                    >
                        {{ flipped ? card.backLanguage : card.frontLanguage }}
                    </span>
                    <span class="text-xl font-medium">{{
                        flipped ? (card.back ?? '—') : (card.front ?? '—')
                    }}</span>
                    <span
                        class="mt-4 text-sm text-[#706f6c] dark:text-[#A1A09A]"
                    >
                        Kattints a fordulathoz
                    </span>
                </button>

                <div v-if="flipped" class="mt-6 grid grid-cols-4 gap-3">
                    <button
                        type="button"
                        class="rounded-sm border border-[#e3e3e0] px-4 py-2 text-sm hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#EDEDEC]"
                        @click="answer('forgot')"
                    >
                        Nem tudtam
                    </button>
                    <button
                        type="button"
                        class="rounded-sm border border-[#e3e3e0] px-4 py-2 text-sm hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#EDEDEC]"
                        @click="answer('hard')"
                    >
                        Nehéz
                    </button>
                    <button
                        type="button"
                        class="rounded-sm border border-[#e3e3e0] px-4 py-2 text-sm hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#EDEDEC]"
                        @click="answer('medium')"
                    >
                        Közepes
                    </button>
                    <button
                        type="button"
                        class="rounded-sm border border-[#e3e3e0] px-4 py-2 text-sm hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#EDEDEC]"
                        @click="answer('easy')"
                    >
                        Könnyű
                    </button>
                </div>
            </div>

            <div
                v-else
                class="w-full rounded-lg bg-white p-6 text-center shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <p class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    Nincs kártya ebben a csoportban.
                </p>
            </div>
        </div>
    </div>
</template>
