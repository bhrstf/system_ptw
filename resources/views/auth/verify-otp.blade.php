<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP - PTW System Batamindo</title>
    
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
        
        /* Input OTP Kotak Putih Bersih */
        .otp-input { 
            background-color: #FFFFFF; 
            border: 2px solid #E5E7EB;
            transition: all 0.2s ease;
        }
        .otp-input:focus { 
            border-color: #003884; 
            box-shadow: 0 0 0 4px rgba(0, 56, 132, 0.1);
            outline: none;
        }
    </style>
</head>
<body class="h-full bg-[#F3F4F6] flex items-center justify-center p-6">

<div class="max-w-md w-full bg-white rounded-[2.5rem] shadow-2xl p-10 text-center border border-gray-100" 
     x-data="otpHandler()">
    
    <div class="bg-blue-50 w-20 h-20 rounded-[1.5rem] flex items-center justify-center mx-auto mb-8 shadow-sm">
        <svg class="w-10 h-10 text-[#003884]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
        </svg>
    </div>

    <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight mb-3">Verifikasi Akun</h2>
    <p class="text-gray-500 font-medium text-sm mb-10 leading-relaxed px-4">
        Masukkan 6 digit kode OTP yang dikirimkan ke email Anda untuk akses sistem.
    </p>

    <form method="POST" action="{{ route('otp.process') }}">
        @csrf
        <div class="flex justify-between gap-2 mb-6">
            <template x-for="(i, index) in 6" :key="index">
                <input 
                    type="text" 
                    maxlength="1" 
                    name="otp[]"
                    class="otp-input w-12 h-16 text-center text-2xl font-bold text-[#003884] rounded-2xl"
                    @input="handleInput($event, index)"
                    @keydown.backspace="handleBackspace($event, index)"
                    x-ref="otpFields"
                    required
                >
            </template>
        </div>

        <div class="mb-8 flex items-center justify-center gap-2">
            <div class="w-2 h-2 rounded-full" :class="seconds > 60 ? 'bg-green-500' : 'bg-red-500 animate-pulse'"></div>
            <p class="text-sm font-semibold text-gray-500">
                Kode berakhir dalam: <span class="text-red-500 font-extrabold" x-text="formatTime()"></span>
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 rounded-2xl border border-red-100">
                <p class="text-red-600 text-xs font-bold italic">{{ $errors->first() }}</p>
            </div>
        @endif

        <button type="submit" class="w-full bg-[#003884] hover:bg-blue-900 text-white py-5 rounded-2xl font-bold text-sm tracking-widest shadow-xl transition-all duration-300 uppercase">
            Verifikasi & Masuk
        </button>
    </form>

    <div class="mt-10 pt-8 border-t border-gray-50">
        <p class="text-gray-400 text-sm font-medium">Tidak menerima kode?</p>
        <button 
            type="button"
            class="mt-2 font-extrabold text-sm transition-all"
            :class="seconds > 0 ? 'text-gray-300 cursor-not-allowed' : 'text-[#003884] hover:underline cursor-pointer'"
            :disabled="seconds > 0"
            @click="resendOtp()"
        >
            Kirim Ulang Kode <span x-show="seconds > 0" x-text="`(${seconds}s)`"></span>
        </button>
    </div>
</div>

<script>
    function otpHandler() {
        return {
            seconds: 120, // 2 Menit sesuai backend
            interval: null,
            
            init() {
                this.startTimer();
                // Auto focus ke kotak pertama saat halaman load
                this.$nextTick(() => {
                    this.$refs.otpFields[0].focus();
                });
            },

            startTimer() {
                this.interval = setInterval(() => {
                    if (this.seconds > 0) {
                        this.seconds--;
                    } else {
                        clearInterval(this.interval);
                    }
                }, 1000);
            },

            formatTime() {
                const mins = Math.floor(this.seconds / 60);
                const secs = this.seconds % 60;
                return `${mins}:${secs.toString().padStart(2, '0')}`;
            },

            handleInput(e, index) {
                const val = e.target.value;
                e.target.value = val.replace(/[^0-9]/g, '');
                
                if (e.target.value && index < 5) {
                    this.$refs.otpFields[index + 1].focus();
                }
            },

            handleBackspace(e, index) {
                if (!e.target.value && index > 0) {
                    this.$refs.otpFields[index - 1].focus();
                }
            },

            resendOtp() {
                if(this.seconds === 0) {
                    // Logic kirim ulang panggil route resend di sini
                    alert('Kode baru sedang dikirim ke email Anda...');
                    window.location.reload(); // Refresh buat simulasi dapet kode baru
                }
            }
        }
    }
</script>

</body>
</html>