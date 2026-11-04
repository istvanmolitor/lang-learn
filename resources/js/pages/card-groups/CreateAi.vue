<script setup lang="ts">
import { storeAi } from '@/actions/App/Http/Controllers/CardGroupController';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    topic: '',
    count: 10,
});

function submit() {
    form.post(storeAi.url());
}
</script>

<template>
    <Head title="Új csoport AI-val" />
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
                <h1 class="mb-1 font-medium">Új csoport AI-val</h1>
                <p class="mb-6 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    Adj meg egy témát, és az AI legenerálja a kártyákat
                    angol-magyar fordítással. A már létező kártyák nem
                    duplázódnak, csak bekerülnek a csoportba.
                </p>

                <form class="flex flex-col gap-6" @submit.prevent="submit">
                    <div class="flex flex-col gap-1">
                        <label for="name" class="text-sm font-medium"
                            >Csoport neve</label
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

                    <div class="flex flex-col gap-1">
                        <label for="topic" class="text-sm font-medium"
                            >Téma</label
                        >
                        <input
                            id="topic"
                            v-model="form.topic"
                            type="text"
                            placeholder="pl. konyhai eszközök, időjárás, utazás"
                            class="rounded-sm border border-[#e3e3e0] bg-transparent px-3 py-1.5 text-sm outline-none focus:border-[#1b1b18] dark:border-[#3E3E3A] dark:focus:border-[#EDEDEC]"
                        />
                        <div
                            v-if="form.errors.topic"
                            class="text-sm text-[#f53003] dark:text-[#FF4433]"
                        >
                            {{ form.errors.topic }}
                        </div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label for="count" class="text-sm font-medium"
                            >Kártyák száma</label
                        >
                        <input
                            id="count"
                            v-model.number="form.count"
                            type="number"
                            min="1"
                            max="30"
                            class="rounded-sm border border-[#e3e3e0] bg-transparent px-3 py-1.5 text-sm outline-none focus:border-[#1b1b18] dark:border-[#3E3E3A] dark:focus:border-[#EDEDEC]"
                        />
                        <div
                            v-if="form.errors.count"
                            class="text-sm text-[#f53003] dark:text-[#FF4433]"
                        >
                            {{ form.errors.count }}
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="mt-2 inline-block rounded-sm border border-black bg-[#1b1b18] px-5 py-1.5 text-sm leading-normal text-white hover:border-black hover:bg-black disabled:opacity-50 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                    >
                        {{ form.processing ? 'Generálás...' : 'Generálás' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
