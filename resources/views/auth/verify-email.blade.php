<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-50">
        <div class="w-full max-w-md bg-white rounded-[2.5rem] shadow-2xl p-8 md:p-12 border border-gray-100 text-center">
            
            <div class="flex justify-center mb-8">
                <x-authentication-card-logo />
            </div>

            <div class="bg-blue-50 w-20 h-20 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-inner">
                <svg class="w-10 h-10 text-[#003884]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>

            <h2 class="text-2xl font-extrabold text-gray-900 mb-2 uppercase tracking-tight">Verifikasi Email Anda</h2>
            
            <div class="mb-6 text-sm text-gray-500 font-medium leading-relaxed">
                {{ __('Terima kasih telah mendaftar! Sebelum mulai, silakan verifikasi akun Anda dengan memasukkan kode OTP yang baru saja kami kirimkan ke email Anda.') }}
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="mb-6 p-4 bg-green-50 text-green-600 rounded-2xl text-xs font-bold border border-green-100 uppercase tracking-widest">
                    {{ __('Kode verifikasi baru telah dikirim!') }}
                </div>
            @endif

            <form method="POST" action="{{ route('otp.process') }}" x-data="otpForm()">
                @csrf
                <div class="flex justify-between gap-2 mb-8">
                    <template x-for="(i, index) in 6" :key="index">
                        <input type="text" 
                            name="otp[]" 
                            maxlength="1" 
                            class="w-11 h-14 text-xl font-black text-center border-2 border-gray-100 bg-gray-50 rounded-xl focus:border-[#003884] focus:ring-4 focus:ring-blue-100 transition-all outline-none"
                            x-on:input="handleInput($event, index)"
                            x-on:keydown.backspace="handleBackspace($event, index)"
                            oninput="this.value=this.value.replace(/[^0-9]/g,'');"
                            required>
                    </template>
                </div>

                <x-button class="w-full justify-center bg-[#003884] hover:bg-blue-800 text-white font-extrabold py-4 rounded-2xl transition-all shadow-lg uppercase tracking-[0.2em]">
                    {{ __('Verifikasi Akun') }}
                </x-button>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-100 flex flex-col gap-4">
                <form method="POST" action="{{ route('otp.resend') }}">
                    @csrf
                    <button type="submit" class="text-sm font-bold text-blue-600 hover:text-blue-800 underline decoration-2 underline-offset-4 uppercase tracking-tighter">
                        {{ __('Kirim Ulang Kode OTP') }}
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-xs font-bold text-gray-400 hover:text-red-500 transition-colors uppercase tracking-widest">
                        {{ __('Keluar / Batalkan') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function otpForm() {
            return {
                handleInput(e, index) {
                    const input = e.target;
                    if (input.value && index < 5) {
                        const inputs = input.closest('form').querySelectorAll('input[type="text"]');
                        inputs[index + 1].focus();
                    }
                },
                handleBackspace(e, index) {
                    const input = e.target;
                    if (!input.value && index > 0) {
                        const inputs = input.closest('form').querySelectorAll('input[type="text"]');
                        inputs[index - 1].focus();
                    }
                }
            }
        }
    </script>
</x-guest-layout>