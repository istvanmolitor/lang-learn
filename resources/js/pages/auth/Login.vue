<script setup lang="ts">
import { store } from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import { Form, Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Bejelentkezés" />
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-[#FDFDFC] p-6 text-[#1b1b18] dark:bg-[#0a0a0a]"
    >
        <div
            class="w-full max-w-sm rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
        >
            <h1 class="mb-1 font-medium">Bejelentkezés</h1>
            <p class="mb-6 text-[13px] text-[#706f6c] dark:text-[#A1A09A]">
                Add meg az e-mail címed és a jelszavad a folytatáshoz.
            </p>

            <Form
                v-bind="store.form()"
                reset-on-error
                #default="{ errors, processing }"
                class="flex flex-col gap-4"
            >
                <div class="flex flex-col gap-1">
                    <label for="email" class="text-sm font-medium"
                        >E-mail</label
                    >
                    <input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        autocomplete="username"
                        class="rounded-sm border border-[#e3e3e0] bg-transparent px-3 py-1.5 text-sm outline-none focus:border-[#1b1b18] dark:border-[#3E3E3A] dark:focus:border-[#EDEDEC]"
                    />
                    <div
                        v-if="errors.email"
                        class="text-sm text-[#f53003] dark:text-[#FF4433]"
                    >
                        {{ errors.email }}
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <label for="password" class="text-sm font-medium"
                        >Jelszó</label
                    >
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="rounded-sm border border-[#e3e3e0] bg-transparent px-3 py-1.5 text-sm outline-none focus:border-[#1b1b18] dark:border-[#3E3E3A] dark:focus:border-[#EDEDEC]"
                    />
                    <div
                        v-if="errors.password"
                        class="text-sm text-[#f53003] dark:text-[#FF4433]"
                    >
                        {{ errors.password }}
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="remember" />
                    Maradjak bejelentkezve
                </label>

                <button
                    type="submit"
                    :disabled="processing"
                    class="mt-2 inline-block rounded-sm border border-black bg-[#1b1b18] px-5 py-1.5 text-sm leading-normal text-white hover:border-black hover:bg-black disabled:opacity-50 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                >
                    {{ processing ? 'Bejelentkezés...' : 'Bejelentkezés' }}
                </button>
            </Form>
        </div>
    </div>
</template>
