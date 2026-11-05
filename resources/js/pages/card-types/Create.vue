<script setup lang="ts">
import { store } from '@/actions/App/Http/Controllers/CardTypeController';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    code: '',
    name: '',
});

function submit() {
    form.post(store.url());
}
</script>

<template>
    <Head title="Új szófaj" />
    <div
        class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a]"
    >
        <div class="w-full max-w-lg">
            <Link
                href="/card-types"
                class="mb-4 inline-block text-sm text-[#706f6c] underline underline-offset-4 hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
            >
                ← Vissza a szófajokhoz
            </Link>

            <div
                class="w-full rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
            >
                <h1 class="mb-6 font-medium">Új szófaj</h1>

                <form class="flex flex-col gap-6" @submit.prevent="submit">
                    <div class="flex flex-col gap-1">
                        <label for="code" class="text-sm font-medium"
                            >Kód</label
                        >
                        <input
                            id="code"
                            v-model="form.code"
                            type="text"
                            placeholder="pl. n, v, adj"
                            class="rounded-sm border border-[#e3e3e0] bg-transparent px-3 py-1.5 text-sm outline-none focus:border-[#1b1b18] dark:border-[#3E3E3A] dark:focus:border-[#EDEDEC]"
                        />
                        <div
                            v-if="form.errors.code"
                            class="text-sm text-[#f53003] dark:text-[#FF4433]"
                        >
                            {{ form.errors.code }}
                        </div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label for="name" class="text-sm font-medium"
                            >Név</label
                        >
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="pl. Főnév"
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
        </div>
    </div>
</template>
