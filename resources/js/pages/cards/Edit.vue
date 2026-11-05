<script setup lang="ts">
import { update } from '@/actions/App/Http/Controllers/CardController';
import { Head, Link, useForm } from '@inertiajs/vue3';

type Language = {
    id: number;
    code: string;
    name: string;
};

type CardGroupOption = {
    id: number;
    name: string;
};

type CardTypeOption = {
    id: number;
    name: string;
};

const props = defineProps<{
    card: {
        id: number;
        translations: Record<number, string>;
        groupIds: number[];
        cardTypeId: number | null;
    };
    languages: Language[];
    groups: CardGroupOption[];
    cardTypes: CardTypeOption[];
}>();

const form = useForm({
    translations: props.languages.map((language) => ({
        language_id: language.id,
        text: props.card.translations[language.id] ?? '',
    })),
    groups: [...props.card.groupIds],
    card_type_id: props.card.cardTypeId,
});

function submit() {
    form.put(update.url(props.card.id));
}
</script>

<template>
    <Head title="Kártya szerkesztése" />
    <div
        class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a]"
    >
        <div class="w-full max-w-lg">
            <Link
                href="/cards"
                class="mb-4 inline-block text-sm text-[#706f6c] underline underline-offset-4 hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
            >
                ← Vissza a kártyákhoz
            </Link>

            <div
                class="w-full rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <h1 class="mb-6 font-medium">Kártya szerkesztése</h1>

                <form class="flex flex-col gap-6" @submit.prevent="submit">
                    <div class="flex flex-col gap-3">
                        <div
                            v-for="(translation, index) in form.translations"
                            :key="translation.language_id"
                            class="flex flex-col gap-1"
                        >
                            <label
                                :for="`translation-${translation.language_id}`"
                                class="text-sm font-medium"
                            >
                                {{
                                    languages.find(
                                        (language) =>
                                            language.id ===
                                            translation.language_id,
                                    )?.name
                                }}
                            </label>
                            <input
                                :id="`translation-${translation.language_id}`"
                                v-model="translation.text"
                                type="text"
                                class="rounded-sm border border-[#e3e3e0] bg-transparent px-3 py-1.5 text-sm outline-none focus:border-[#1b1b18] dark:border-[#3E3E3A] dark:focus:border-[#EDEDEC]"
                            />
                            <div
                                v-if="form.errors[`translations.${index}.text`]"
                                class="text-sm text-[#f53003] dark:text-[#FF4433]"
                            >
                                {{ form.errors[`translations.${index}.text`] }}
                            </div>
                        </div>
                        <p
                            v-if="languages.length === 0"
                            class="text-sm text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            Még nincs nyelv.
                        </p>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label for="card_type_id" class="text-sm font-medium"
                            >Szófaj</label
                        >
                        <select
                            id="card_type_id"
                            v-model="form.card_type_id"
                            class="rounded-sm border border-[#e3e3e0] bg-transparent px-3 py-1.5 text-sm outline-none focus:border-[#1b1b18] dark:border-[#3E3E3A] dark:focus:border-[#EDEDEC]"
                        >
                            <option :value="null">Nincs megadva</option>
                            <option
                                v-for="cardType in cardTypes"
                                :key="cardType.id"
                                :value="cardType.id"
                            >
                                {{ cardType.name }}
                            </option>
                        </select>
                        <div
                            v-if="form.errors.card_type_id"
                            class="text-sm text-[#f53003] dark:text-[#FF4433]"
                        >
                            {{ form.errors.card_type_id }}
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <span class="text-sm font-medium">Csoportok</span>
                        <label
                            v-for="group in groups"
                            :key="group.id"
                            class="flex items-center gap-2 text-sm"
                        >
                            <input
                                type="checkbox"
                                v-model="form.groups"
                                :value="group.id"
                            />
                            {{ group.name }}
                        </label>
                        <p
                            v-if="groups.length === 0"
                            class="text-sm text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            Még nincs csoport.
                        </p>
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
