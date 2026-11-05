<script setup lang="ts">
import {
    create,
    destroy,
    edit,
    index,
} from '@/actions/App/Http/Controllers/CardController';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

type CardListItem = {
    id: number;
    translations: { language_code: string; text: string }[];
    groups: string[];
    type: string | null;
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
    cards: Paginated<CardListItem>;
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
                <div class="flex items-center gap-4">
                    <Link
                        href="/card-types"
                        class="text-sm text-[#706f6c] underline underline-offset-4 hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                    >
                        Szófajok
                    </Link>
                    <Link
                        :href="create()"
                        class="text-sm text-[#706f6c] underline underline-offset-4 hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                    >
                        + Új kártya
                    </Link>
                </div>
            </div>

            <div
                class="w-full rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <h1 class="mb-6 font-medium">Kártyák</h1>

                <input
                    v-model="search"
                    type="search"
                    placeholder="Keresés..."
                    class="mb-6 w-full rounded-sm border border-[#e3e3e0] bg-transparent px-3 py-1.5 text-sm outline-none focus:border-[#1b1b18] dark:border-[#3E3E3A] dark:focus:border-[#EDEDEC]"
                />

                <p
                    v-if="cards.data.length === 0"
                    class="text-sm text-[#706f6c] dark:text-[#A1A09A]"
                >
                    Nincs a keresésnek megfelelő kártya.
                </p>

                <ul v-else class="flex flex-col gap-4">
                    <li
                        v-for="card in cards.data"
                        :key="card.id"
                        class="flex items-start justify-between gap-4 border-b border-[#e3e3e0] pb-4 last:border-0 last:pb-0 dark:border-[#3E3E3A]"
                    >
                        <div class="flex flex-col gap-1">
                            <div
                                class="flex flex-wrap items-center gap-3 text-sm"
                            >
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
                                <span
                                    v-if="card.type"
                                    class="rounded-full bg-[#f5f5f4] px-2 py-0.5 text-xs text-[#706f6c] dark:bg-[#1f1f1d] dark:text-[#A1A09A]"
                                >
                                    {{ card.type }}
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

                <nav
                    v-if="cards.links.length > 3"
                    class="mt-6 flex flex-wrap items-center gap-1"
                >
                    <template v-for="(link, index) in cards.links" :key="index">
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
