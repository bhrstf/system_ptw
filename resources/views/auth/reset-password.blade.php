<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center bg-slate-50 p-6 font-sans text-slate-900">
        <div class="w-full max-w-[440px]">
            <div class="bg-white rounded-[2.5rem] shadow-[0_20px_50px_rgba(8,112,184,0.07)] border border-gray-100 overflow-hidden">
                
                <div class="pt-12 pb-6 px-10 text-center">
                    <div class="flex justify-center mb-6">
                        <div class="p-4 bg-blue-50 rounded-3xl">
                            <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                            </svg>
                        </div>
                    </div>
                    <h1 class="text-3xl font-black text-slate-800 tracking-tight">
                        PTW <span class="text-blue-600">SYSTEM</span>
                    </h1>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.3em] mt-2 italic">Batamindo Project</p>
                </div>

                <div class="px-10 pb-12">
                    <div class="bg-slate-50/50 rounded-3xl p-8 border border-slate-100/50">
                        <h2 class="text-lg font-bold text-slate-800 text-center mb-6">Buat Password Baru</h2>

                        <x-validation-errors class="mb-6" />

                        <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                            @csrf

                            <input type="hidden" name="token" value="{{ $request->route('token') }}">

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 ml-1">Email</label>
                                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required readonly 
                                       class="w-full px-6 py-4 bg-slate-100 border border-slate-200 rounded-2xl text-sm text-slate-500 outline-none cursor-not-allowed shadow-sm">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 ml-1">Password Baru</label>
                                <input id="password" type="password" name="password" required autofocus autocomplete="new-password"
                                       class="w-full px-6 py-4 bg-white border border-slate-200 focus:border-blue-600 focus:ring-4 focus:ring-blue-100 rounded-2xl text-sm transition-all duration-300 outline-none shadow-sm">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 ml-1">Konfirmasi Password</label>
                                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                                       class="w-full px-6 py-4 bg-white border border-slate-200 focus:border-blue-600 focus:ring-4 focus:ring-blue-100 rounded-2xl text-sm transition-all duration-300 outline-none shadow-sm">
                            </div>

                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-2xl shadow-lg shadow-blue-600/20 transition-all transform active:scale-95 text-xs uppercase tracking-widest mt-4">
                                Simpan Password Baru
                            </button>
                        </form>
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