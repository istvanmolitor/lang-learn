<script setup lang="ts">
import { store } from '@/actions/App/Http/Controllers/BookController';
import { Head, Link, useForm } from '@inertiajs/vue3';

type LanguageOption = {
    id: number;
    name: string;
};

const props = defineProps<{
    languages: LanguageOption[];
}>();

const form = useForm({
    file: null as File | null,
    language_id: props.languages[0]?.id ?? 0,
});

function onFileChange(event: Event) {
    form.file = (event.target as HTMLInputElement).files?.[0] ?? null;
}

function submit() {
    form.post(store.url(), {
        forceFormData: true,
    });
}
</script>

<template>
    <Head title="Könyv feltöltése" />
    <div
        class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a]"
    >
        <div class="w-full max-w-lg">
            <Link
                href="/books"
                class="mb-4 inline-block text-sm text-[#706f6c] underline underline-offset-4 hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
            >
                ← Vissza a könyvekhez
            </Link>

            <div
                class="w-full rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <h1 class="mb-1 font-medium">Könyv feltöltése</h1>
                <p class="mb-6 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    Egy EPUB fájl feltöltésével a könyv szövege oldalakra,
                    bekezdésekre és mondatokra bontva bekerül a könyvek közé.
                    A feldolgozás a háttérben történik, ez eltarthat néhány
                    percig.
                </p>

                <form class="flex flex-col gap-6" @submit.prevent="submit">
                    <div class="flex flex-col gap-1">
                        <label for="language_id" class="text-sm font-medium"
                            >Nyelv</label
                        >
                        <select
                            id="language_id"
                            v-model.number="form.language_id"
                            class="rounded-sm border border-[#e3e3e0] bg-transparent px-3 py-1.5 text-sm outline-none focus:border-[#1b1b18] dark:border-[#3E3E3A] dark:focus:border-[#EDEDEC]"
                        >
                            <option
                                v-for="language in languages"
                                :key="language.id"
                                :value="language.id"
                            >
                                {{ language.name }}
                            </option>
                        </select>
                        <div
                            v-if="form.errors.language_id"
                            class="text-sm text-[#f53003] dark:text-[#FF4433]"
                        >
                            {{ form.errors.language_id }}
                        </div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label for="file" class="text-sm font-medium"
                            >EPUB fájl</label
                        >
                        <input
                            id="file"
                            type="file"
                            accept=".epub,application/epub+zip"
                            class="text-sm"
                            @change="onFileChange"
                        />
                        <div
                            v-if="form.errors.file"
                            class="text-sm text-[#f53003] dark:text-[#FF4433]"
                        >
                            {{ form.errors.file }}
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing || !form.file"
                        class="mt-2 inline-block w-fit rounded-sm border border-black bg-[#1b1b18] px-5 py-1.5 text-sm leading-normal text-white hover:border-black hover:bg-black disabled:opacity-50 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                    >
                        {{ form.processing ? 'Feltöltés...' : 'Feltöltés' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
