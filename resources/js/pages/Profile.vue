<script setup lang="ts">
import { update } from '@/actions/App/Http/Controllers/ProfileController';
import { Head, Link, useForm } from '@inertiajs/vue3';

type Language = {
    id: number;
    code: string;
    name: string;
    native_name: string;
};

type UserLanguage = {
    language_id: number;
    level: string;
};

const props = defineProps<{
    languages: Language[];
    nativeLanguageId: number | null;
    userLanguages: UserLanguage[];
}>();

const levels = [
    { value: 'a1', label: 'A1 – kezdő' },
    { value: 'a2', label: 'A2 – alapszintű' },
    { value: 'b1', label: 'B1 – középszintű' },
    { value: 'b2', label: 'B2 – középszintű+' },
    { value: 'c1', label: 'C1 – haladó' },
    { value: 'c2', label: 'C2 – anyanyelvi szintű' },
];

const form = useForm({
    native_language_id: props.nativeLanguageId,
    languages: props.userLanguages.map((userLanguage) => ({ ...userLanguage })),
});

function addLanguage() {
    form.languages.push({
        language_id: props.languages[0]?.id ?? 0,
        level: 'a1',
    });
}

function removeLanguage(index: number) {
    form.languages.splice(index, 1);
}

function submit() {
    form.put(update.url());
}
</script>

<template>
    <Head title="Profil" />
    <div
        class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a]"
    >
        <div class="w-full max-w-lg">
            <Link
                href="/"
                class="mb-4 inline-block text-sm text-[#706f6c] underline underline-offset-4 hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
            >
                ← Vissza
            </Link>

            <div
                class="w-full rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <h1 class="mb-1 font-medium">Profil</h1>
                <p class="mb-6 text-[13px] text-[#706f6c] dark:text-[#A1A09A]">
                    Állítsd be az anyanyelvedet és a nyelveket, amelyeket
                    ismersz.
                </p>

                <form class="flex flex-col gap-6" @submit.prevent="submit">
                    <div class="flex flex-col gap-1">
                        <label
                            for="native_language_id"
                            class="text-sm font-medium"
                            >Anyanyelv</label
                        >
                        <select
                            id="native_language_id"
                            v-model="form.native_language_id"
                            class="rounded-sm border border-[#e3e3e0] bg-transparent px-3 py-1.5 text-sm outline-none focus:border-[#1b1b18] dark:border-[#3E3E3A] dark:focus:border-[#EDEDEC]"
                        >
                            <option :value="null">Nincs megadva</option>
                            <option
                                v-for="language in languages"
                                :key="language.id"
                                :value="language.id"
                            >
                                {{ language.name }} ({{ language.native_name }})
                            </option>
                        </select>
                        <div
                            v-if="form.errors.native_language_id"
                            class="text-sm text-[#f53003] dark:text-[#FF4433]"
                        >
                            {{ form.errors.native_language_id }}
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium"
                                >Ismert nyelvek</span
                            >
                            <button
                                type="button"
                                class="text-sm text-[#706f6c] underline underline-offset-4 hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                                @click="addLanguage"
                            >
                                + Nyelv hozzáadása
                            </button>
                        </div>

                        <div
                            v-for="(userLanguage, index) in form.languages"
                            :key="index"
                            class="flex items-center gap-2"
                        >
                            <select
                                v-model="userLanguage.language_id"
                                class="flex-1 rounded-sm border border-[#e3e3e0] bg-transparent px-3 py-1.5 text-sm outline-none focus:border-[#1b1b18] dark:border-[#3E3E3A] dark:focus:border-[#EDEDEC]"
                            >
                                <option
                                    v-for="language in languages"
                                    :key="language.id"
                                    :value="language.id"
                                >
                                    {{ language.name }}
                                </option>
                            </select>

                            <select
                                v-model="userLanguage.level"
                                class="rounded-sm border border-[#e3e3e0] bg-transparent px-3 py-1.5 text-sm outline-none focus:border-[#1b1b18] dark:border-[#3E3E3A] dark:focus:border-[#EDEDEC]"
                            >
                                <option
                                    v-for="level in levels"
                                    :key="level.value"
                                    :value="level.value"
                                >
                                    {{ level.label }}
                                </option>
                            </select>

                            <button
                                type="button"
                                class="text-sm text-[#f53003] dark:text-[#FF4433]"
                                @click="removeLanguage(index)"
                            >
                                Törlés
                            </button>
                        </div>

                        <div
                            v-if="form.errors.languages"
                            class="text-sm text-[#f53003] dark:text-[#FF4433]"
                        >
                            {{ form.errors.languages }}
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
        </div>
    </div>
</template>
