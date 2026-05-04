<x-guest-layout>
    <head>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
        
        <style>
            body { 
                font-family: 'Plus Jakarta Sans', sans-serif !important; 
                -webkit-font-smoothing: antialiased;
                background-color: #F8FAFC !important;
            }
            
            /* Kunci agar layout Jetstream tidak merusak ukuran card */
            .fixed-card-container {
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                min-height: 100vh !important;
                width: 100% !important;
                padding: 24px !important;
                box-sizing: border-box !important;
                background-color: #F8FAFC !important;
            }

            .card-wrapper {
                width: 100% !important;
                max-width: 440px !important; 
                margin: 0 auto !important;
                display: block !important;
            }

            .input-soft { 
                background-color: #F8FAFC !important; 
                border: 1px solid #E2E8F0 !important;
                transition: all 0.2s ease !important;
                display: block !important;
                width: 100% !important;
            }
            .input-soft:focus { 
                background-color: #FFFFFF !important; 
                border-color: #003884 !important; 
                box-shadow: 0 0 0 4px rgba(0, 56, 132, 0.05) !important;
                outline: none !important;
            }
            .input-readonly {
                background-color: #F1F5F9 !important;
                border: 1px solid #E2E8F0 !important;
                color: #64748B !important;
                cursor: not-allowed !important;
                display: block !important;
                width: 100% !important;
            }
            
            /* Wrapper relatif untuk mengunci tombol mata di dalam input */
            .eye-wrapper {
                position: relative !important;
                width: 100% !important;
                display: block !important;
            }
            
            .eye-button {
                position: absolute !important;
                top: 50% !important;
                right: 16px !important;
                transform: translateY(-50%) !important;
                z-index: 20 !important;
                background: transparent !important;
                border: none !important;
                cursor: pointer !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                padding: 4px !important;
                color: #94A3B8 !important;
            }
            .eye-button:hover {
                color: #475569 !important;
            }
        </style>
    </head>

    <div class="fixed-card-container">
        <div class="card-wrapper">
            
            <div class="bg-white rounded-[2.5rem] shadow-[0_20px_60px_rgba(0,56,132,0.06)] overflow-hidden border-0 transition-all duration-500">
                
                <div class="pt-14 pb-8 px-10 text-center select-none">
                    <div class="flex justify-center mb-6">
                        <div class="p-4 bg-blue-50/80 rounded-[1.5rem] text-[#003884] shadow-sm">
                            <svg class="w-9 h-9" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-.99.43-1.563A6 6 0 1121.75 8.25z"/>
                            </svg>
                        </div>
                    </div>
                    <h1 class="text-3xl font-[800] text-slate-900 tracking-tight leading-none">
                        PTW <span class="text-blue-600">SYSTEM</span>
                    </h1>
                    <div class="mt-3.5 flex items-center justify-center gap-2">
                        <span class="h-[1px] w-4 bg-slate-200"></span>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.3em]">Batamindo Project</p>
                        <span class="h-[1px] w-4 bg-slate-200"></span>
                    </div>
                </div>

                <div class="px-10 pb-12">
                    <div class="bg-slate-50/60 rounded-[2.2rem] p-8">
                        <h2 class="text-lg font-extrabold text-slate-800 text-center mb-2 tracking-tight">Buat Password Baru</h2>
                        <p class="text-slate-500 text-xs text-center leading-relaxed mb-8 select-none">
                            Silakan masukkan password baru Anda untuk mengamankan akun.
                        </p>

                        @if ($errors->any())
                            <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-100 text-red-600 text-[11px] font-bold leading-relaxed">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.update') }}" class="space-y-6" x-data="{ showPassword: false, showConfirmPassword: false }">
                            @csrf

                            <input type="hidden" name="token" value="{{ $request->route('token') }}">

                            <div>
                                <label class="text-[10px] font-[800] text-slate-400 uppercase tracking-widest mb-2 block ml-1 select-none">Email</label>
                                <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required readonly 
                                       class="input-readonly w-full px-5 py-4 rounded-2xl text-sm font-semibold select-none outline-none">
                            </div>

                            <div>
                                <label class="text-[10px] font-[800] text-slate-400 uppercase tracking-widest mb-2 block ml-1 select-none">Password Baru</label>
                                <div class="eye-wrapper">
                                    <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required autofocus autocomplete="new-password"
                                           class="input-soft w-full px-5 py-4 pr-12 rounded-2xl text-sm font-semibold text-slate-700 placeholder-slate-300 outline-none"
                                           placeholder="••••••••">
                                    <button type="button" @click="showPassword = !showPassword" class="eye-button focus:outline-none">
                                        <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display: none;">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="text-[10px] font-[800] text-slate-400 uppercase tracking-widest mb-2 block ml-1 select-none">Konfirmasi Password</label>
                                <div class="eye-wrapper">
                                    <input id="password_confirmation" :type="showConfirmPassword ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password"
                                           class="input-soft w-full px-5 py-4 pr-12 rounded-2xl text-sm font-semibold text-slate-700 placeholder-slate-300 outline-none"
                                           placeholder="••••••••">
                                    <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="eye-button focus:outline-none">
                                        <svg x-show="!showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        <svg x-show="showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display: none;">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="w-full bg-[#003884] hover:bg-blue-900 text-white font-extrabold py-4 rounded-2xl shadow-xl transition-all duration-300 hover:shadow-blue-900/10 active:scale-[0.98] text-xs uppercase tracking-widest mt-2 flex items-center justify-center gap-2">
                                <span>Simpan Password</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="mt-10 text-center select-none">
                <p class="text-[9px] font-bold text-slate-300 uppercase tracking-[0.5em]">
                    &copy; {{ date('Y') }} PTW SYSTEM
                </p>
            </div>
        </div>
    </div>
</x-guest-layout>