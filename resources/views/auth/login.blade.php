<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In - PTW System Batamindo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    
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

        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 md:p-16 lg:p-24 bg-white">
            <div class="w-full max-w-md">
                
                <div class="mb-10">
                    <h2 class="text-3xl font-[800] text-gray-900 tracking-tighter leading-none">Log In</h2>
                    <p class="text-gray-400 mt-3 font-medium text-sm">Selamat datang kembali! Masuk ke akun Anda.</p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label class="text-[10px] font-[800] text-gray-400 uppercase tracking-widest mb-2 block ml-1">Alamat Email</label>
                        <input type="email" name="email" required autofocus
                            class="w-full px-5 py-4 rounded-2xl input-soft text-sm font-semibold text-gray-700"
                            placeholder="nama@email.com">
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-2 px-1">
                            <label class="text-[10px] font-[800] text-gray-400 uppercase tracking-widest">Password</label>
                            <a href="{{ route('password.request') }}" class="text-[10px] font-bold text-blue-600 hover:underline uppercase tracking-widest">Lupa?</a>
                        </div>
                        <input type="password" name="password" required
                            class="w-full px-5 py-4 rounded-2xl input-soft text-sm font-semibold text-gray-700"
                            placeholder="••••••••">
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