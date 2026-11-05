<script setup lang="ts">
import { destroy } from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import { index as booksIndex } from '@/actions/App/Http/Controllers/BookController';
import { index as cardGroupsIndex } from '@/actions/App/Http/Controllers/CardGroupController';
import { index as cardsIndex } from '@/actions/App/Http/Controllers/CardController';
import { edit } from '@/actions/App/Http/Controllers/ProfileController';
import { update } from '@/actions/App/Http/Controllers/SelectedLanguageController';
import { index as wordsIndex } from '@/actions/App/Http/Controllers/WordController';
import { Head, Link, router } from '@inertiajs/vue3';

type Language = {
    id: number;
    code: string;
    name: string;
    native_name: string;
};

const props = defineProps<{
    learnableLanguages: Language[];
    selectedLanguageId: number | null;
}>();

function selectLanguage(event: Event) {
    const languageId = Number((event.target as HTMLSelectElement).value);

    router.put(
        update.url(),
        { language_id: languageId },
        { preserveScroll: true },
    );
}
</script>

<template>
    <Head title="Dashboard" />
    <div
        class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:p-8 dark:bg-[#0a0a0a]"
    >
        <div class="flex w-full max-w-2xl items-center justify-between gap-4">
            <div
                v-if="learnableLanguages.length > 0"
                class="flex items-center gap-2"
            >
                <label
                    for="selected_language_id"
                    class="text-sm text-[#706f6c] dark:text-[#A1A09A]"
                >
                    Tanult nyelv
                </label>
                <select
                    id="selected_language_id"
                    :value="props.selectedLanguageId ?? ''"
                    class="rounded-sm border border-[#e3e3e0] bg-transparent px-3 py-1.5 text-sm outline-none focus:border-[#1b1b18] dark:border-[#3E3E3A] dark:focus:border-[#EDEDEC]"
                    @change="selectLanguage"
                >
                    <option value="" disabled>Válassz nyelvet</option>
                    <option
                        v-for="language in learnableLanguages"
                        :key="language.id"
                        :value="language.id"
                    >
                        {{ language.name }} ({{ language.native_name }})
                    </option>
                </select>
            </div>
            <p v-else class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                Még nincs ismert nyelved —
                <Link
                    :href="edit()"
                    class="underline underline-offset-4 hover:text-[#1b1b18] dark:hover:text-[#EDEDEC]"
                >
                    add meg a profilodban </Link
                >.
            </p>

            <div class="flex items-center gap-4">
                <Link
                    :href="cardsIndex()"
                    class="text-sm text-[#706f6c] underline underline-offset-4 hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                >
                    Kártyák
                </Link>
                <Link
                    :href="cardGroupsIndex()"
                    class="text-sm text-[#706f6c] underline underline-offset-4 hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                >
                    Csoportok
                </Link>
                <Link
                    :href="booksIndex()"
                    class="text-sm text-[#706f6c] underline underline-offset-4 hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                >
                    Könyvek
                </Link>
                <Link
                    :href="wordsIndex()"
                    class="text-sm text-[#706f6c] underline underline-offset-4 hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                >
                    Szavak
                </Link>
                <Link
                    :href="edit()"
                    class="text-sm text-[#706f6c] underline underline-offset-4 hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                >
                    Profil
                </Link>
                <Link
                    :href="destroy()"
                    class="text-sm text-[#706f6c] underline underline-offset-4 hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                >
                    Kijelentkezés
                </Link>
            </div>
        </div>

        <div class="mt-8 w-full max-w-2xl">
            <div
                class="w-full rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <h1 class="mb-1 font-medium">Dashboard</h1>
                <p class="text-[13px] text-[#706f6c] dark:text-[#A1A09A]">
                    Be vagy jelentkezve.
                </p>
            </div>
        </div>
    </div>
</template>
