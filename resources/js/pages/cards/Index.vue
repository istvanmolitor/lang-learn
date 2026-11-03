<script setup lang="ts">
import {
    create,
    destroy,
    edit,
} from '@/actions/App/Http/Controllers/CardController';
import { Head, Link, router } from '@inertiajs/vue3';

type CardListItem = {
    id: number;
    translations: { language_code: string; text: string }[];
    groups: string[];
};

defineProps<{
    cards: CardListItem[];
}>();

function remove(cardId: number) {
    if (window.confirm('Biztosan törlöd ezt a kártyát?')) {
        router.delete(destroy.url(cardId));
    }
}
</script>

<template>
    <Head title="Kártyák" />
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
                    + Új kártya
                </Link>
            </div>

            <div
                class="w-full rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <h1 class="mb-6 font-medium">Kártyák</h1>

                <p
                    v-if="cards.length === 0"
                    class="text-sm text-[#706f6c] dark:text-[#A1A09A]"
                >
                    Még nincs kártya.
                </p>

                <ul v-else class="flex flex-col gap-4">
                    <li
                        v-for="card in cards"
                        :key="card.id"
                        class="flex items-start justify-between gap-4 border-b border-[#e3e3e0] pb-4 last:border-0 last:pb-0 dark:border-[#3E3E3A]"
                    >
                        <div class="flex flex-col gap-1">
                            <div class="flex flex-wrap gap-3 text-sm">
                                <span
                                    v-for="translation in card.translations"
                                    :key="translation.language_code"
                                >
                                    <span
                                        class="text-[#706f6c] dark:text-[#A1A09A]"
                                    >
                                        {{
                                            translation.language_code.toUpperCase()
                                        }}:
                                    </span>
                                    {{ translation.text }}
                                </span>
                                <span
                                    v-if="card.translations.length === 0"
                                    class="text-[#706f6c] dark:text-[#A1A09A]"
                                >
                                    Nincs még szöveg.
                                </span>
                            </div>
                            <div
                                v-if="card.groups.length > 0"
                                class="flex flex-wrap gap-2"
                            >
                                <span
                                    v-for="group in card.groups"
                                    :key="group"
                                    class="rounded-full bg-[#f5f5f4] px-2 py-0.5 text-xs text-[#706f6c] dark:bg-[#1f1f1d] dark:text-[#A1A09A]"
                                >
                                    {{ group }}
                                </span>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-3 text-sm">
                            <Link
                                :href="edit(card.id)"
                                class="text-[#706f6c] underline underline-offset-4 hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                            >
                                Szerkesztés
                            </Link>
                            <button
                                type="button"
                                class="text-[#f53003] dark:text-[#FF4433]"
                                @click="remove(card.id)"
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
