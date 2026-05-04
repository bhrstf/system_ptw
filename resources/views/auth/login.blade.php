<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - PTW System Batamindo</title>
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
        .input-soft { 
            background-color: #F8FAFC; 
            border: 1px solid #E2E8F0;
            transition: all 0.2s ease;
        }
        .input-soft:focus { 
            background-color: #FFFFFF; 
            border-color: #003884; 
            box-shadow: 0 0 0 4px rgba(0, 56, 132, 0.05);
            outline: none;
        }
    </style>
</head>
<body class="h-full bg-white antialiased text-gray-900">

    <div class="flex min-h-screen">
        <div class="hidden lg:flex lg:w-1/2 bg-navy-batamindo items-center justify-center p-12 relative overflow-hidden">
            <div class="max-w-xl text-center z-10">
                <div class="bg-white w-24 h-24 rounded-[2rem] flex items-center justify-center mx-auto mb-10 shadow-xl">
                    <svg class="w-14 h-14 text-[#003884]" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 3C7.03 3 3 7.03 3 12V15C3 15.55 3.45 16 4 16H5V21H19V16H20C20.55 16 21 15.55 21 15V12C21 7.03 16.97 3 12 3M12 5C15.87 5 19 8.13 19 12V14H5V12C5 8.13 8.13 5 12 5Z"/>
                    </svg>
                </div>

                <h1 class="text-4xl xl:text-5xl font-[800] text-white mb-6 leading-[1.1] tracking-tight">
                    Mulai Kelola <br> <span class="text-blue-300">Perizinan Digital</span>
                </h1>
                
                <p class="text-blue-100 text-lg font-medium mb-16 max-w-sm mx-auto leading-relaxed opacity-90">
                    Sistem Permit to Work (PTW) Batamindo. <br> Cepat, Aman, dan Paperless.
                </p>

                <div class="flex items-center justify-center gap-12 pt-10 border-t border-white/10">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-white mb-1">100%</div>
                        <div class="text-[10px] font-bold uppercase tracking-widest text-blue-300">Digital</div>
                    </div>
                    <div class="h-10 w-[1px] bg-white/10"></div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-white mb-1">Secure</div>
                        <div class="text-[10px] font-bold uppercase tracking-widest text-blue-300">Platform</div>
                    </div>
                    <div class="h-10 w-[1px] bg-white/10"></div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-white mb-1">Fast</div>
                        <div class="text-[10px] font-bold uppercase tracking-widest text-blue-300">Approval</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 md:p-16 lg:p-24 bg-white" x-data="{ showPassword: false }">
            <div class="w-full max-w-md">
                
                <div class="mb-10">
                    <h2 class="text-3xl font-[800] text-gray-900 tracking-tighter leading-none">Log In</h2>
                    <p class="text-gray-400 mt-3 font-medium text-sm">Selamat datang kembali! Masuk ke akun Anda.</p>
                </div>

                @if ($errors->any())
    <div class="mb-7 p-5 rounded-2xl bg-red-50 border border-red-100 shadow-sm flex gap-4 items-start">
        <div class="flex-shrink-0 w-11 h-11 rounded-full bg-red-500 flex items-center justify-center shadow-md border-2 border-white">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </div>
        
        <div class="flex-1 mt-1">
            <h3 class="text-sm font-extrabold text-red-900 uppercase tracking-wider mb-1.5">
                Gagal Masuk
            </h3>
            <ul class="list-disc list-inside space-y-1 text-xs text-red-800 font-semibold leading-relaxed">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label class="text-[10px] font-[800] text-gray-400 uppercase tracking-widest mb-2 block ml-1">Alamat Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full px-5 py-4 rounded-2xl input-soft text-sm font-semibold text-gray-700 @error('email') border-red-300 bg-red-50/30 @enderror"
                            placeholder="nama@email.com">
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2 px-1">
                            <label class="text-[10px] font-[800] text-gray-400 uppercase tracking-widest">Password</label>
                            <a href="{{ route('password.request') }}" class="text-[10px] font-bold text-blue-600 hover:underline uppercase tracking-widest">Lupa?</a>
                        </div>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" name="password" required
                                class="w-full px-5 py-4 pr-12 rounded-2xl input-soft text-sm font-semibold text-gray-700 @error('email') border-red-300 bg-red-50/30 @enderror"
                                placeholder="••••••••">
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
                    </div>

                    <div class="flex items-center ml-1">
                        <input type="checkbox" id="remember_me" name="remember" class="w-4 h-4 text-[#003884] border-gray-300 rounded focus:ring-[#003884]">
                        <label for="remember_me" class="ml-3 text-sm text-gray-500 font-semibold">Ingat perangkat ini</label>
                    </div>

                    <button type="submit" class="w-full bg-[#003884] hover:bg-blue-900 text-white py-5 rounded-2xl font-bold text-sm tracking-wide shadow-xl transition-all duration-300 mt-4">
                        Masuk Sekarang
                    </button>
                </form>

                <p class="mt-10 text-center text-sm text-gray-400 font-medium">
                    Belum punya akun? <a href="{{ route('register') }}" class="text-[#003884] font-bold hover:underline ml-1">Daftar Sekarang</a>
                </p>
            </div>
        </div>
    </div>

</body>
</html>