<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemulihan Akun - PTW System</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        /* Mengaplikasikan font premium secara global */
        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            -webkit-font-smoothing: antialiased;
        }
        
        /* Gaya input-soft modern: Transisi halus, background soft, shadow focus */
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
<body class="h-full text-slate-900">

    <div class="min-h-screen flex flex-col items-center justify-center p-6 bg-slate-50">
        
        <div class="w-full max-w-md transition-all duration-500">
            <div class="bg-white rounded-[2.5rem] shadow-[0_20px_60px_rgba(0,56,132,0.06)] border border-gray-100/70 overflow-hidden">
                
                <div class="pt-14 pb-8 px-10 text-center">
                    <div class="flex justify-center mb-6">
                        <div class="p-4 bg-blue-50/80 rounded-[1.5rem] text-[#003884] shadow-sm">
                            <svg class="w-9 h-9" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                            </svg>
                        </div>
                    </div>
                    <h1 class="text-3xl font-[800] text-slate-900 tracking-tight leading-none">
                        PTW <span class="text-blue-600 font-[800]">SYSTEM</span>
                    </h1>
                    <div class="mt-3 flex items-center justify-center gap-2">
                        <span class="h-[1px] w-4 bg-slate-200"></span>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.3em]">Batamindo Project</p>
                        <span class="h-[1px] w-4 bg-slate-200"></span>
                    </div>
                </div>

                <div class="px-10 pb-12">
                    <div class="bg-slate-50/60 rounded-[2.2rem] p-8 border border-slate-100/60">
                        <h2 class="text-lg font-extrabold text-slate-800 text-center mb-2.5 tracking-tight">Pemulihan Akun</h2>
                        <p class="text-slate-500 text-xs text-center leading-relaxed mb-8 px-1">
                            Masukkan email Anda yang terdaftar untuk mendapatkan instruksi link reset password.
                        </p>

                        @if (session('status'))
                            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-700 text-[11px] font-bold text-center leading-relaxed animate-pulse">
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-100 text-red-600 text-[11px] font-bold leading-relaxed px-5 py-3.5">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                            @csrf
                            <div>
                                <label class="text-[10px] font-[800] text-gray-400 uppercase tracking-widest mb-2 block ml-1">Alamat Email</label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus 
                                       class="w-full px-5 py-4 rounded-2xl input-soft text-sm font-semibold text-gray-700 placeholder-gray-300" 
                                       placeholder="nama@email.com">
                            </div>

                            <button type="submit" class="w-full bg-[#003884] hover:bg-blue-900 text-white font-extrabold py-4 rounded-2xl shadow-xl transition-all duration-300 hover:shadow-blue-900/10 active:scale-[0.98] text-xs uppercase tracking-widest mt-2 flex items-center justify-center gap-2">
                                <span>Kirim Instruksi</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3"/></svg>
                            </button>
                        </form>

                        <div class="mt-8 text-center border-t border-slate-200/60 pt-6">
                            <a href="{{ route('login') }}" class="text-[11px] font-bold text-slate-400 hover:text-[#003884] transition-all uppercase tracking-wider flex items-center justify-center gap-1.5 group">
                                <span class="transition-transform group-hover:-translate-x-0.5">←</span>
                                <span>Kembali ke Login</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-10 text-center">
                <p class="text-[9px] font-bold text-slate-300 uppercase tracking-[0.5em]">
                    &copy; {{ date('Y') }} PTW SYSTEM
                </p>
            </div>
        </div>
    </div>
</body>
</html>