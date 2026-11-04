<script setup lang="ts">
import {
    exportMethod,
    generateCards,
    importMethod,
    update,
} from '@/actions/App/Http/Controllers/CardGroupController';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    group: {
        id: number;
        name: string;
    };
    generated?: {
        created: number;
        reused: number;
    } | null;
}>();

const form = useForm({
    name: props.group.name,
});

function submit() {
    form.put(update.url(props.group.id));
}

const generateForm = useForm({
    topic: '',
    count: 10,
});

function submitGenerate() {
    generateForm.post(generateCards.url(props.group.id));
}

const importForm = useForm({
    file: null as File | null,
});

function onFileChange(event: Event) {
    importForm.file = (event.target as HTMLInputElement).files?.[0] ?? null;
}

function submitImport() {
    importForm.post(importMethod.url(props.group.id), {
        forceFormData: true,
    });
}
</script>

<template>
    <Head title="Csoport szerkesztése" />
    <div
        class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a]"
    >
        <div class="w-full max-w-lg">
            <Link
                href="/card-groups"
                class="mb-4 inline-block text-sm text-[#706f6c] underline underline-offset-4 hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
            >
                ← Vissza a csoportokhoz
            </Link>

            <div
                class="w-full rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <h1 class="mb-6 font-medium">Csoport szerkesztése</h1>

                <form class="flex flex-col gap-6" @submit.prevent="submit">
                    <div class="flex flex-col gap-1">
                        <label for="name" class="text-sm font-medium"
                            >Név</label
                        >
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="rounded-sm border border-[#e3e3e0] bg-transparent px-3 py-1.5 text-sm outline-none focus:border-[#1b1b18] dark:border-[#3E3E3A] dark:focus:border-[#EDEDEC]"
                        />
                        <div
                            v-if="form.errors.name"
                            class="text-sm text-[#f53003] dark:text-[#FF4433]"
                        >
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="mt-2 inline-block rounded-sm border border-black bg-[#1b1b18] px-5 py-1.5 text-sm leading-normal text-white hover:border-black hover:bg-black disabled:opacity-50 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                    >
                        {{ form.processing ? 'Mentés...' : 'Mentés' }}
                    </button>
                </form>
            </div>

            <div
                class="mt-6 w-full rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <h2 class="mb-1 font-medium">Kártyák generálása AI-val</h2>
                <p class="mb-6 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    A már létező kártyák nem duplázódnak, csak bekerülnek a
                    csoportba.
                </p>

                <div
                    v-if="generated"
                    class="mb-6 rounded-sm border border-[#e3e3e0] px-3 py-2 text-sm dark:border-[#3E3E3A]"
                >
                    {{ generated.created }} új kártya létrehozva,
                    {{ generated.reused }} meglévő kártya hozzáadva a
                    csoporthoz.
                </div>

                <form
                    class="flex flex-col gap-6"
                    @submit.prevent="submitGenerate"
                >
                    <div class="flex flex-col gap-1">
                        <label for="gen-topic" class="text-sm font-medium"
                            >Téma</label
                        >
                        <input
                            id="gen-topic"
                            v-model="generateForm.topic"
                            type="text"
                            placeholder="pl. konyhai eszközök, időjárás, utazás"
                            class="rounded-sm border border-[#e3e3e0] bg-transparent px-3 py-1.5 text-sm outline-none focus:border-[#1b1b18] dark:border-[#3E3E3A] dark:focus:border-[#EDEDEC]"
                        />
                        <div
                            v-if="generateForm.errors.topic"
                            class="text-sm text-[#f53003] dark:text-[#FF4433]"
                        >
                            {{ generateForm.errors.topic }}
                        </div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label for="gen-count" class="text-sm font-medium"
                            >Kártyák száma</label
                        >
                        <input
                            id="gen-count"
                            v-model.number="generateForm.count"
                            type="number"
                            min="1"
                            max="30"
                            class="rounded-sm border border-[#e3e3e0] bg-transparent px-3 py-1.5 text-sm outline-none focus:border-[#1b1b18] dark:border-[#3E3E3A] dark:focus:border-[#EDEDEC]"
                        />
                        <div
                            v-if="generateForm.errors.count"
                            class="text-sm text-[#f53003] dark:text-[#FF4433]"
                        >
                            {{ generateForm.errors.count }}
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="generateForm.processing"
                        class="mt-2 inline-block w-fit rounded-sm border border-black bg-[#1b1b18] px-5 py-1.5 text-sm leading-normal text-white hover:border-black hover:bg-black disabled:opacity-50 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                    >
                        {{
                            generateForm.processing
                                ? 'Generálás...'
                                : 'Generálás'
                        }}
                    </button>
                </form>
            </div>

            <div
                class="mt-6 w-full rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <h2 class="mb-6 font-medium">Import / export</h2>

                <div class="flex flex-col gap-6">
                    <div class="flex flex-col gap-1">
                        <span class="text-sm font-medium">Exportálás</span>
                        <a
                            :href="exportMethod.url(group.id)"
                            class="inline-block w-fit rounded-sm border border-[#e3e3e0] px-5 py-1.5 text-sm leading-normal hover:border-[#1b1b18] dark:border-[#3E3E3A] dark:hover:border-[#EDEDEC]"
                        >
                            CSV letöltése
                        </a>
                    </div>

                    <form
                        class="flex flex-col gap-1"
                        @submit.prevent="submitImport"
                    >
                        <label for="import-file" class="text-sm font-medium"
                            >Importálás CSV fájlból</label
                        >
                        <input
                            id="import-file"
                            type="file"
                            accept=".csv,text/csv"
                            class="text-sm"
                            @change="onFileChange"
                        />
                        <div
                            v-if="importForm.errors.file"
                            class="text-sm text-[#f53003] dark:text-[#FF4433]"
                        >
                            {{ importForm.errors.file }}
                        </div>
                        <button
                            type="submit"
                            :disabled="
                                importForm.processing || !importForm.file
                            "
                            class="mt-2 inline-block w-fit rounded-sm border border-[#e3e3e0] px-5 py-1.5 text-sm leading-normal hover:border-[#1b1b18] disabled:opacity-50 dark:border-[#3E3E3A] dark:hover:border-[#EDEDEC]"
                        >
                            {{
                                importForm.processing
                                    ? 'Importálás...'
                                    : 'Importálás'
                            }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
