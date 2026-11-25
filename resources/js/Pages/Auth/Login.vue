<script setup>
import TravelAccountAside from '@/Components/ui/TravelAccountAside.vue';
import PageHeading from '@/Components/ui/PageHeading.vue';
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useAppContext } from '@/composables/useAppContext';
import { ShieldCheck, Building2, UserCheck, Lock, Mail, ArrowRight, Search, Eye, EyeOff } from 'lucide-vue-next';

const props = defineProps({
    demoAccounts: { type: Array, default: () => [] },
    allPengelolaAccounts: { type: Array, default: () => [] },
    redirectUrl: { type: String, default: '' },
    reason: { type: String, default: '' },
});

const { t } = useAppContext();
const showPassword = ref(false);
const searchPengelola = ref('');

const filteredPengelola = computed(() => {
    const q = searchPengelola.value.trim().toLowerCase();
    if (!q) return props.allPengelolaAccounts;
    return props.allPengelolaAccounts.filter((acc) => {
        const dName = (acc.destinasi?.nama_destinasi || '').toLowerCase();
        const wName = (acc.destinasi?.wilayah?.nama_wilayah || '').toLowerCase();
        return dName.includes(q) || wName.includes(q) || acc.email.toLowerCase().includes(q);
    });
});

const form = useForm({
    email: '',
    password: '',
    remember: true,
    redirect: props.redirectUrl || '',
});

const fillDemo = (acc) => {
    form.email = acc.email;
    form.password = 'password';
    form.redirect = props.redirectUrl || '';
    form.post('/login');
};

const submit = () => {
    form.redirect = props.redirectUrl || '';
    form.post('/login');
};
</script>

<template>
    <AppLayout>
        <Head :title="t('Masuk Akun', 'Sign In')" />
        <section class="mx-auto w-full max-w-[1440px] px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
            <PageHeading :title="t('Selamat datang kembali', 'Welcome back')" :section="t('Masuk akun', 'Sign in')" :description="t('Masuk untuk merencanakan perjalanan atau mengelola layanan pariwisata.', 'Sign in to plan your trip or manage tourism services.')" />
            <p v-if="reason === 'detail' || redirectUrl" role="status" class="mb-6 flex items-center gap-2 rounded-xl border border-utama/30 bg-utama-muda/40 p-4 text-sm text-tinta"><Lock class="h-4 w-4 shrink-0 text-utama" />{{ t('Silakan masuk untuk melanjutkan ke halaman yang Anda pilih.', 'Please sign in to continue to your selected page.') }}</p>
            <div class="grid items-start gap-6 lg:grid-cols-12 lg:gap-8">
                <div class="kartu-togean p-6 sm:p-8 lg:col-span-5">
                    <h2 class="font-heading text-xl font-bold text-tinta">{{ t('Masuk ke akun Anda', 'Sign in to your account') }}</h2>
                    <p class="mt-2 text-sm text-tinta-redup">{{ t('Gunakan email dan kata sandi yang terdaftar.', 'Use your registered email address and password.') }}</p>
                    <form @submit.prevent="submit" class="mt-6 space-y-5">
                        <div>
                            <label for="email" class="mb-2 block text-sm font-semibold text-tinta">{{ t('Alamat email', 'Email address') }}</label>
                            <div class="relative"><Mail class="pointer-events-none absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-tinta-redup" /><input id="email" v-model="form.email" type="email" autocomplete="username" required :aria-invalid="!!form.errors.email" :aria-describedby="form.errors.email ? 'login-email-error' : undefined" :placeholder="t('nama@email.com', 'name@email.com')" class="w-full rounded-xl border border-garis bg-permukaan-redup pr-4 pl-10 text-tinta" /></div>
                            <p v-if="form.errors.email" id="login-email-error" role="alert" class="mt-2 text-sm text-bahaya">{{ form.errors.email }}</p>
                        </div>
                        <div>
                            <label for="password" class="mb-2 block text-sm font-semibold text-tinta">{{ t('Kata sandi', 'Password') }}</label>
                            <div class="relative"><Lock class="pointer-events-none absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-tinta-redup" /><input id="password" v-model="form.password" :type="showPassword ? 'text' : 'password'" autocomplete="current-password" required class="w-full rounded-xl border border-garis bg-permukaan-redup pr-12 pl-10 text-tinta" /><button type="button" @click="showPassword = !showPassword" :aria-label="showPassword ? t('Sembunyikan kata sandi', 'Hide password') : t('Tampilkan kata sandi', 'Show password')" :aria-pressed="showPassword" class="absolute inset-y-0 right-0 flex w-12 cursor-pointer items-center justify-center rounded-r-xl text-tinta-redup hover:text-utama"><EyeOff v-if="showPassword" class="h-4 w-4" /><Eye v-else class="h-4 w-4" /></button></div>
                        </div>
                        <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-tinta-redup"><input v-model="form.remember" type="checkbox" />{{ t('Ingat saya di perangkat ini', 'Remember me on this device') }}</label>
                        <button type="submit" :disabled="form.processing" class="tombol-utama flex w-full cursor-pointer items-center justify-center gap-2 px-5 disabled:cursor-wait disabled:opacity-60"><span>{{ form.processing ? t('Memverifikasi?', 'Signing in?') : t('Masuk', 'Sign in') }}</span><ArrowRight class="h-4 w-4" /></button>
                    </form>
                    <p class="mt-6 border-t border-garis pt-5 text-sm text-tinta-redup">{{ t('Belum punya akun wisatawan?', 'New to Jelajah Sulteng?') }} <Link href="/register" class="font-semibold text-utama-teks underline underline-offset-2">{{ t('Daftar akun', 'Create an account') }}</Link></p>
                </div>
                <div class="space-y-5 lg:col-span-7">
<TravelAccountAside />
                    <details class="kartu-togean p-6"><summary class="cursor-pointer font-heading text-base font-bold text-tinta">{{ t('Coba sistem dengan akun demo', 'Try a demo account') }}</summary>
                        
                        <p class="mt-2 text-sm text-tinta-redup">{{ t('Pilih peran untuk melihat layanan wisatawan, pengelola destinasi, atau admin dinas.', 'Choose a role to explore traveller services, destination operations, or the tourism office dashboard.') }}</p>
                        <div class="mt-5 space-y-3">
                            <button v-for="account in demoAccounts" :key="account.id" type="button" @click="fillDemo(account)" :disabled="form.processing" class="flex w-full cursor-pointer items-center gap-3 rounded-xl border border-garis bg-permukaan-redup p-4 text-left transition hover:border-utama hover:bg-utama-muda/30 disabled:opacity-60">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-utama-muda text-utama-teks"><ShieldCheck v-if="account.peran === 'admin'" class="h-5 w-5" /><Building2 v-else-if="account.peran === 'pengelola_destinasi'" class="h-5 w-5" /><UserCheck v-else class="h-5 w-5" /></span>
                                <span class="min-w-0 flex-1"><strong class="block text-sm text-tinta">{{ account.name }}</strong><span class="block break-all text-xs text-tinta-redup">{{ account.email }}</span></span><ArrowRight class="h-4 w-4 shrink-0 text-utama" />
                            </button>
                        </div>
                        <p class="mt-4 text-xs text-tinta-redup">{{ t('Kata sandi akun demo:', 'Demo account password:') }} <code class="rounded bg-permukaan-redup px-2 py-1 font-mono">password</code></p>
                    </details>
                    <details class="kartu-togean p-6">
                        <summary class="cursor-pointer font-heading text-base font-bold text-tinta">{{ t('Akun pengelola per destinasi', 'Destination manager accounts') }} <span class="ml-1 font-sans text-xs font-medium text-tinta-redup">({{ allPengelolaAccounts.length }})</span></summary>
                        <label for="manager-search" class="mt-4 mb-2 block text-sm text-tinta-redup">{{ t('Cari destinasi, wilayah, atau email', 'Search destination, region, or email') }}</label>
                        <div class="relative"><Search class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-tinta-redup" /><input id="manager-search" v-model="searchPengelola" type="search" class="w-full rounded-xl border border-garis pr-3 pl-9" /></div>
                        <div class="mt-4 max-h-[400px] space-y-2 overflow-y-auto pr-1">
                            <button v-for="account in filteredPengelola" :key="account.id" type="button" @click="fillDemo(account)" :disabled="form.processing" class="flex w-full cursor-pointer items-center justify-between gap-3 rounded-xl border border-garis p-3 text-left hover:border-utama disabled:opacity-60"><span class="min-w-0"><strong class="block text-sm text-tinta">{{ account.destinasi?.nama_destinasi || account.name }}</strong><span class="block break-all text-xs text-tinta-redup">{{ account.email }}</span></span><ArrowRight class="h-4 w-4 shrink-0 text-utama" /></button>
                            <p v-if="!filteredPengelola.length" class="py-4 text-sm text-tinta-redup">{{ t('Tidak ada akun yang sesuai dengan pencarian.', 'No accounts match your search.') }}</p>
                        </div>
                    </details>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
