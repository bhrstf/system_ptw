<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-slate-50 p-6 font-sans text-slate-900">
        <div class="w-full max-w-[440px]">
            <div class="bg-white rounded-[2.5rem] shadow-[0_20px_50px_rgba(8,112,184,0.07)] border border-gray-100 overflow-hidden">
                
                <div class="pt-12 pb-6 px-10 text-center">
                    <div class="flex justify-center mb-6">
                        <div class="p-4 bg-blue-50 rounded-3xl">
                            <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                    </div>
                    <h1 class="text-3xl font-black text-slate-800 tracking-tight">
                        PTW <span class="text-blue-600">SYSTEM</span>
                    </h1>
                    <div class="mt-2 flex items-center justify-center gap-2">
                        <span class="h-[1px] w-4 bg-slate-200"></span>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.3em]">Batamindo Project</p>
                        <span class="h-[1px] w-4 bg-slate-200"></span>
                    </div>
                </div>

                <div class="px-10 pb-12">
                    <div class="bg-slate-50/50 rounded-3xl p-8 border border-slate-100/50">
                        <h2 class="text-lg font-bold text-slate-800 text-center mb-2">Pemulihan Akun</h2>
                        <p class="text-slate-500 text-xs text-center leading-relaxed mb-8">
                            Masukkan email kamu untuk mendapatkan link reset password.
                        </p>

                        @if (session('status'))
                            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-[11px] font-bold text-center">
                                {{ session('status') }}
                            </div>
                        @endif

                        <x-validation-errors class="mb-6" />

                        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                            @csrf
                            <div>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus 
                                       class="w-full px-6 py-4 bg-white border border-slate-200 focus:border-blue-600 focus:ring-4 focus:ring-blue-100 rounded-2xl text-sm transition-all duration-300 outline-none shadow-sm" 
                                       placeholder="Masukkan email kamu">
                            </div>

                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-blue-600/20 transition-all transform active:scale-95 text-xs uppercase tracking-widest mt-2">
                                Kirim Instruksi
                            </button>
                        </form>

                        <div class="mt-8 text-center border-t border-slate-100 pt-6">
                            <a href="{{ route('login') }}" class="text-[11px] font-bold text-slate-400 hover:text-blue-600 transition-all uppercase tracking-wider">
                                ← Kembali ke Login
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 text-center">
                <p class="text-[9px] font-bold text-slate-300 uppercase tracking-[0.5em]">
                    &copy; {{ date('Y') }} PTW SYSTEM
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>