<script setup>
import PageHeading from '@/Components/ui/PageHeading.vue';
import TravelAccountAside from '@/Components/ui/TravelAccountAside.vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useAppContext } from '@/composables/useAppContext';
const { t } = useAppContext();

const form = useForm({
    name: '',
    email: '',
    asal_wilayah: 'Sulawesi Tengah',
    bahasa: 'id',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register');
};
</script>

<template>
    <AppLayout>
        <Head :title="t('Daftar Akun Wisatawan', 'Create a traveller account')" />

        <section class="mx-auto max-w-[1440px] px-4 py-8 sm:px-6 lg:px-8"><PageHeading :title="t('Daftar Akun Wisatawan', 'Create a traveller account')" :section="t('Daftar akun', 'Register')" :description="t('Simpan destinasi, susun perjalanan, dan akses tiket wisata Anda.', 'Save destinations, plan your trip, and access your tourism tickets.')" /><div class="grid items-start gap-6 lg:grid-cols-2 lg:gap-8">
            <div class="kartu-togean p-6 sm:p-8">
                <h2 class="font-heading text-xl font-bold text-tinta">{{ t('Buat akun Anda', 'Create your account') }}</h2>
                <p class="mt-3 text-sm text-[var(--warna-teks-redup)]">{{ t('Lengkapi data berikut untuk mulai menjelajah.', 'Enter your details to start exploring.') }}</p>

                <form @submit.prevent="submit" class="mt-6 space-y-4">
                    <div>
                        <label for="field-auth-register-form-name" class="mb-1 block text-xs font-bold">{{ t("Nama Lengkap", "Full name") }}</label>
                        <input :aria-invalid="Boolean(form.errors.name)" id="field-auth-register-form-name"
                            v-model="form.name"
                            type="text"
                            required
                            :placeholder="t('Nama lengkap Anda', 'Your full name')"
                            class="h-11 w-full rounded-xl border border-[var(--warna-garis)] bg-[var(--warna-pasir)] px-3.5 text-sm"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs font-semibold text-[#C2492E]">! {{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label for="field-auth-register-form-email" class="mb-1 block text-xs font-bold">{{ t("Alamat Email", "Email address") }}</label>
                        <input :aria-invalid="Boolean(form.errors.email)" id="field-auth-register-form-email"
                            v-model="form.email"
                            type="email"
                            required
                            placeholder="nama@email.com"
                            class="h-11 w-full rounded-xl border border-[var(--warna-garis)] bg-[var(--warna-pasir)] px-3.5 text-sm"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-xs font-semibold text-[#C2492E]">! {{ form.errors.email }}</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="field-auth-register-form-asal_wilayah" class="mb-1 block text-xs font-bold">{{ t("Provinsi / Negara Asal", "Home region / country") }}</label>
                            <input :aria-invalid="Boolean(form.errors.asal_wilayah)" id="field-auth-register-form-asal_wilayah"
                                v-model="form.asal_wilayah"
                                type="text"
                                class="h-11 w-full rounded-xl border border-[var(--warna-garis)] bg-[var(--warna-pasir)] px-3.5 text-sm"
                            />
                        </div>
                        <div>
                            <label for="field-auth-register-form-bahasa" class="mb-1 block text-xs font-bold">{{ t("Bahasa Utama", "Preferred language") }}</label>
                            <select :aria-invalid="Boolean(form.errors.bahasa)" id="field-auth-register-form-bahasa"
                                v-model="form.bahasa"
                                class="h-11 w-full rounded-xl border border-[var(--warna-garis)] bg-[var(--warna-pasir)] px-3 text-sm"
                            >
                                <option value="id">{{ t("Bahasa Indonesia", "Indonesian") }}</option>
                                <option value="en">English</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="field-auth-register-form-password" class="mb-1 block text-xs font-bold">{{ t("Kata Sandi", "Password") }}</label>
                            <input :aria-invalid="Boolean(form.errors.password)" id="field-auth-register-form-password"
                                v-model="form.password"
                                type="password"
                                autocomplete="new-password"
                                minlength="6"
                                required
                                class="h-11 w-full rounded-xl border border-[var(--warna-garis)] bg-[var(--warna-pasir)] px-3.5 text-sm"
                            />
                            <p v-if="form.errors.password" class="mt-1 text-xs font-semibold text-[#C2492E]">! {{ form.errors.password }}</p>
                        </div>
                        <div>
                            <label for="field-auth-register-form-password_confirmation" class="mb-1 block text-xs font-bold">{{ t("Konfirmasi Kata Sandi", "Confirm password") }}</label>
                            <input :aria-invalid="Boolean(form.errors.password_confirmation)" id="field-auth-register-form-password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                required
                                class="h-11 w-full rounded-xl border border-[var(--warna-garis)] bg-[var(--warna-pasir)] px-3.5 text-sm"
                            />
                        </div>
                    </div>

                    <button type="submit" :disabled="form.processing" class="tombol-utama w-full text-sm">
                        {{ form.processing ? t('Mendaftarkan…', 'Creating account…') : t('Buat akun wisatawan', 'Create traveller account') }}
                    </button>
                </form>

                <div class="mt-5 text-center text-xs text-[var(--warna-teks-redup)]"> {{ t("Sudah punya akun?", "Already have an account?") }} <Link href="/login" class="font-bold text-[#0A5F67] underline">{{ t("Masuk di sini", "Sign in") }}</Link>
                </div>
            </div>
<TravelAccountAside />
</div>
        </section>
    </AppLayout>
</template>

