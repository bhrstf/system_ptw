<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - PTW System</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            -webkit-font-smoothing: antialiased;
        }
        .bg-navy-batamindo { background-color: #003884; }
        
        .input-modern { 
            background-color: #F9FAFB; 
            border: 1px solid #E5E7EB;
            transition: all 0.2s ease;
        }
        .input-modern:focus { 
            background-color: #FFFFFF; 
            border-color: #003884; 
            box-shadow: 0 0 0 4px rgba(0, 56, 132, 0.05);
            outline: none;
        }
        .input-error {
            border-color: #ef4444 !important;
            background-color: #fef2f2 !important;
        }
    </style>
</head>
<body class="h-full bg-white text-gray-900">

<div class="flex min-h-screen">
    <div class="hidden lg:flex lg:w-1/2 bg-navy-batamindo items-center justify-center p-12 relative">
        <div class="max-w-xl text-center z-10">
            <div class="bg-white w-24 h-24 rounded-[2rem] flex items-center justify-center mx-auto mb-10 shadow-xl">
                <svg class="w-14 h-14 text-[#003884]" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 3C7.03 3 3 7.03 3 12V15C3 15.55 3.45 16 4 16H5V21H19V16H20C20.55 16 21 15.55 21 15V12C21 7.03 16.97 3 12 3M12 5C15.87 5 19 8.13 19 12V14H5V12C5 8.13 8.13 5 12 5Z"/>
                </svg>
            </div>

            <h1 class="text-4xl font-extrabold text-white mb-6 leading-tight tracking-tight">
                Mulai Kelola <br> <span class="text-blue-300 font-bold">Perizinan Digital</span>
            </h1>
            
            <p class="text-blue-100 text-lg font-medium mb-16 max-w-sm mx-auto opacity-90 leading-relaxed">
                Sistem Permit to Work Batamindo. <br> Cepat, Aman, dan Paperless.
            </p>

            <div class="flex items-center justify-center gap-12 pt-10 border-t border-white/10">
                <div class="text-center">
                    <div class="text-2xl font-bold text-white mb-1 uppercase tracking-tight">100%</div>
                    <div class="text-[10px] font-bold tracking-widest text-blue-300">Digital</div>
                </div>
                <div class="h-10 w-[1px] bg-white/10"></div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-white mb-1 tracking-tight">Secure</div>
                    <div class="text-[10px] font-bold tracking-widest text-blue-300">Platform</div>
                </div>
                <div class="h-10 w-[1px] bg-white/10"></div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-white mb-1 tracking-tight">Fast</div>
                    <div class="text-[10px] font-bold tracking-widest text-blue-300">Approval</div>
                </div>
            </div>
        </div>
    </div>

    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-16 bg-white overflow-y-auto">
        <div class="w-full max-w-md" x-data="{ role: '{{ old('role', 'HSE') }}', showPassword: false, showConfirmPassword: false }">
            
            <div class="mb-10">
                <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Buat Akun</h2>
                <p class="text-gray-500 mt-2 font-medium text-sm">Lengkapi data Anda untuk akses sistem.</p>
            </div>

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2 block ml-1">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name') }}" required autofocus 
                        class="w-full px-5 py-4 rounded-2xl input-modern text-sm font-semibold text-gray-700 placeholder-gray-300 @error('name') input-error @enderror" 
                        placeholder="Masukkan nama lengkap Anda">
                    @error('name')
                        <p class="text-red-500 text-[10px] mt-1 ml-1 font-bold italic">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2 block ml-1">Daftar Sebagai</label>
                    <select name="role" x-model="role" class="w-full px-5 py-4 rounded-2xl input-modern text-sm font-semibold text-gray-700 appearance-none cursor-pointer">
                        <option value="HSE">HSE / Safety Batamindo</option>
                        <option value="Kontraktor">Kontraktor</option>
                    </select>
                </div>

                <div x-show="role === 'HSE'" class="space-y-5" x-transition:enter="transition ease-out duration-300">
                    <div>
                        <label class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2 block ml-1">NPK</label>
                        <input type="text" name="username" value="{{ old('username') }}" 
                            class="w-full px-5 py-4 rounded-2xl input-modern text-sm font-semibold text-gray-700 @error('username') input-error @enderror" 
                            placeholder="Masukkan NPK Anda">
                        @error('username')
                            <p class="text-red-500 text-[10px] mt-1 ml-1 font-bold italic">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="text-[11px] font-bold text-red-600 uppercase tracking-widest mb-2 block ml-1 italic">Kode Verifikasi Khusus HSE</label>
                        <input type="password" name="verification_code" 
                            class="w-full px-5 py-4 rounded-2xl input-modern border-red-200 text-sm font-semibold text-gray-700 @error('verification_code') input-error @enderror" 
                            placeholder="Masukkan kode rahasia kantor">
                        @error('verification_code')
                            <p class="text-red-500 text-[10px] mt-1 ml-1 font-bold italic">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div x-show="role === 'Kontraktor'" x-transition:enter="transition ease-out duration-300">
                    <label class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2 block ml-1">Nama Perusahaan</label>
                    <input type="text" name="company" value="{{ old('company') }}" 
                        class="w-full px-5 py-4 rounded-2xl input-modern text-sm font-semibold text-gray-700 @error('company') input-error @enderror" 
                        placeholder="Contoh: PT. Maju Jaya">
                    @error('company')
                        <p class="text-red-500 text-[10px] mt-1 ml-1 font-bold italic">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2 block ml-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required 
                        class="w-full px-5 py-4 rounded-2xl input-modern text-sm font-semibold text-gray-700 @error('email') input-error @enderror" 
                        placeholder="email@contoh.com">
                    @error('email')
                        <p class="text-red-500 text-[10px] mt-1 ml-1 font-bold italic">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2 block ml-1">Password</label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" name="password" required 
                                class="w-full px-5 py-4 pr-12 rounded-2xl input-modern text-sm font-semibold text-gray-700 @error('password') input-error @enderror" 
                                placeholder="••••">
                            <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg x-show="showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <p class="text-red-500 text-[10px] mt-1 ml-1 font-bold italic">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-2 block ml-1">Konfirmasi</label>
                        <div class="relative">
                            <input :type="showConfirmPassword ? 'text' : 'password'" name="password_confirmation" required 
                                class="w-full px-5 py-4 pr-12 rounded-2xl input-modern text-sm font-semibold text-gray-700" 
                                placeholder="••••">
                            <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                <svg x-show="!showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <svg x-show="showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-[#003884] hover:bg-blue-900 text-white py-5 rounded-2xl font-bold text-sm tracking-widest shadow-xl transition-all duration-300 mt-4 uppercase">
                    Daftar Sekarang
                </button>
            </form>

            <p class="text-center text-sm text-gray-400 mt-10 font-medium italic">
                Sudah punya akun? <a href="{{ route('login') }}" class="text-[#003884] font-bold not-italic hover:underline ml-1">Masuk di sini</a>
            </p>

        </div>
    </div>
</div>

</body>
</html>