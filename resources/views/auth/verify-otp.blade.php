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
        /* Menghilangkan spin button pada input number di Chrome/Safari */
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* ========== RESPONSIVE DESIGN ========== */
        
        /* Desktop (1024px+) */
        @media (min-width: 1024px) {
            body { 
                padding: 1.5rem; 
            }
            .otp-container {
                padding: 2.5rem;
                border-radius: 2.5rem;
                max-width: 28rem;
            }
            .icon-container {
                width: 5rem;
                height: 5rem;
                margin-bottom: 2rem;
            }
            .icon-svg {
                width: 2.5rem;
                height: 2.5rem;
            }
            .title {
                font-size: 1.875rem;
                margin-bottom: 0.75rem;
            }
            .subtitle {
                font-size: 0.875rem;
                margin-bottom: 2.5rem;
                padding: 0 1rem;
            }
            .otp-box {
                gap: 0.5rem;
                margin-bottom: 1.5rem;
            }
            .otp-input {
                width: 3rem;
                height: 4rem;
                font-size: 1.5rem;
            }
            .timer-section {
                margin-bottom: 2rem;
            }
            .timer-text {
                font-size: 0.875rem;
            }
            .error-message {
                margin-bottom: 1.5rem;
                padding: 1rem;
                font-size: 0.75rem;
            }
            .btn-submit {
                padding: 1.25rem;
                font-size: 0.875rem;
            }
            .footer-section {
                margin-top: 2.5rem;
                padding-top: 2rem;
            }
            .footer-text {
                font-size: 0.875rem;
            }
            .btn-resend {
                margin-top: 0.5rem;
                font-size: 0.875rem;
            }
        }

        /* Text alignment untuk responsif */
        .otp-container {
            text-align: center;
        }

        @media (max-width: 767px) {
            .footer-text {
                text-align: center;
            }
            .btn-resend {
                text-align: center;
                display: block;
                width: 100%;
            }
        }

        /* Tablet (768px - 1023px) */
        @media (min-width: 768px) and (max-width: 1023px) {
            body { 
                padding: 1.25rem; 
            }
            .otp-container {
                padding: 2rem;
                border-radius: 2rem;
                max-width: 26rem;
            }
            .icon-container {
                width: 4.5rem;
                height: 4.5rem;
                margin-bottom: 1.5rem;
            }
            .icon-svg {
                width: 2.25rem;
                height: 2.25rem;
            }
            .title {
                font-size: 1.75rem;
                margin-bottom: 0.65rem;
            }
            .subtitle {
                font-size: 0.825rem;
                margin-bottom: 2rem;
                padding: 0 1rem;
            }
            .otp-box {
                gap: 0.4rem;
                margin-bottom: 1.5rem;
            }
            .otp-input {
                width: 2.75rem;
                height: 3.75rem;
                font-size: 1.375rem;
            }
            .timer-section {
                margin-bottom: 2rem;
            }
            .timer-text {
                font-size: 0.8rem;
            }
            .error-message {
                margin-bottom: 1.5rem;
                padding: 0.875rem;
                font-size: 0.7rem;
            }
            .btn-submit {
                padding: 1.125rem;
                font-size: 0.8rem;
            }
            .footer-section {
                margin-top: 2.5rem;
                padding-top: 1.75rem;
            }
            .footer-text {
                font-size: 0.8rem;
            }
            .btn-resend {
                margin-top: 0.5rem;
                font-size: 0.8rem;
            }
        }

        /* Mobile (< 768px) */
        @media (max-width: 767px) {
            body { 
                padding: 1rem; 
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
            }
            .otp-container {
                padding: 1.5rem;
                border-radius: 1.5rem;
                width: 100%;
                max-width: 100%;
            }
            .icon-container {
                width: 3.5rem;
                height: 3.5rem;
                margin-bottom: 1rem;
            }
            .icon-svg {
                width: 1.75rem;
                height: 1.75rem;
            }
            .title {
                font-size: 1.5rem;
                margin-bottom: 0.5rem;
            }
            .subtitle {
                font-size: 0.75rem;
                margin-bottom: 1.5rem;
                padding: 0 0.75rem;
                line-height: 1.4;
            }
            .otp-box {
                gap: 0.35rem;
                margin-bottom: 1.25rem;
                justify-content: center;
                flex-wrap: wrap;
            }
            .otp-input {
                width: 2.5rem;
                height: 3.25rem;
                font-size: 1.25rem;
                padding: 0.25rem;
            }
            .timer-section {
                margin-bottom: 1.5rem;
                gap: 0.75rem;
            }
            .timer-dot {
                width: 0.4rem;
                height: 0.4rem;
            }
            .timer-text {
                font-size: 0.7rem;
                line-height: 1.2;
            }
            .error-message {
                margin-bottom: 1rem;
                padding: 0.75rem;
                font-size: 0.65rem;
                border-radius: 1rem;
            }
            .btn-submit {
                padding: 0.875rem;
                font-size: 0.75rem;
                letter-spacing: 0.05em;
                border-radius: 1rem;
            }
            .footer-section {
                margin-top: 1.5rem;
                padding-top: 1rem;
                border-top-width: 1px;
            }
            .footer-text {
                font-size: 0.7rem;
                margin-bottom: 0.5rem;
            }
            .btn-resend {
                margin-top: 0.4rem;
                font-size: 0.7rem;
            }
        }

        /* Small Mobile (< 480px) */
        @media (max-width: 479px) {
            body { 
                padding: 0.75rem; 
            }
            .otp-container {
                padding: 1.25rem;
                border-radius: 1.25rem;
            }
            .icon-container {
                width: 3rem;
                height: 3rem;
                margin-bottom: 0.75rem;
            }
            .icon-svg {
                width: 1.5rem;
                height: 1.5rem;
            }
            .title {
                font-size: 1.25rem;
                margin-bottom: 0.4rem;
            }
            .subtitle {
                font-size: 0.7rem;
                margin-bottom: 1.25rem;
                padding: 0 0.5rem;
            }
            .otp-box {
                gap: 0.3rem;
                margin-bottom: 1rem;
            }
            .otp-input {
                width: 2.25rem;
                height: 3rem;
                font-size: 1.125rem;
                border-radius: 1rem;
            }
            .timer-section {
                margin-bottom: 1.25rem;
                gap: 0.5rem;
            }
            .timer-text {
                font-size: 0.65rem;
            }
            .btn-submit {
                padding: 0.75rem;
                font-size: 0.7rem;
                border-radius: 0.875rem;
            }
            .footer-section {
                margin-top: 1rem;
                padding-top: 0.75rem;
            }
            .footer-text {
                font-size: 0.65rem;
            }
            .btn-resend {
                font-size: 0.65rem;
            }
        }
    </style>
</head>
<body class="h-full bg-[#F3F4F6] flex items-center justify-center">

<div class="otp-container max-w-md w-full bg-white shadow-2xl border border-gray-100 text-center" 
     x-data="otpHandler()">
    
    <div class="icon-container bg-blue-50 flex items-center justify-center mx-auto shadow-sm">
        <svg class="icon-svg text-[#003884]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
        </svg>
    </div>

    <h2 class="title font-extrabold text-gray-900 tracking-tight">Verifikasi Akun</h2>
    <p class="subtitle text-gray-500 font-medium leading-relaxed">
        Masukkan 6 digit kode OTP yang dikirimkan ke email Anda untuk akses sistem.
    </p>

    <form method="POST" action="{{ route('otp.process') }}">
        @csrf
        <div class="otp-box flex justify-between mb-6" x-data="{ otp: ['', '', '', '', '', ''] }">
            <template x-for="(i, index) in 6" :key="index">
                <input 
                    type="text" 
                    maxlength="1" 
                    inputmode="numeric"
                    name="otp[]"
                    class="otp-input text-center font-bold text-[#003884] rounded-2xl"
                    x-model="otp[index]"
                    @input="handleInput($event, index)"
                    @keydown.backspace="handleBackspace($event, index)"
                    @paste="handlePaste($event)"
                    x-ref="otpFields"
                    required
                >
            </template>
        </div>

        <div class="timer-section mb-8 flex items-center justify-center gap-2">
            <div class="timer-dot rounded-full" :class="seconds > 60 ? 'bg-green-500' : 'bg-red-500 animate-pulse'"></div>
            <p class="timer-text font-semibold text-gray-500">
                Kode berakhir dalam: <span class="text-red-500 font-extrabold" x-text="formatTime()"></span>
            </p>
        </div>

        @if ($errors->any())
            <div class="error-message bg-red-50 border border-red-100">
                <p class="text-red-600 font-bold italic">{{ $errors->first() }}</p>
            </div>
        @endif

        <button type="submit" class="btn-submit w-full bg-[#003884] hover:bg-blue-900 text-white font-bold tracking-widest shadow-xl transition-all duration-300 uppercase">
            Verifikasi & Masuk
        </button>
    </form>

    <div class="footer-section pt-8 border-t border-gray-50">
        <p class="footer-text text-gray-400 font-medium text-center">Tidak menerima kode?</p>
        <button 
            type="button"
            class="btn-resend w-full font-extrabold transition-all"
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
            seconds: 120, 
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
                const input = e.target;
                
                // Hanya terima angka
                input.value = input.value.replace(/[^0-9]/g, '');
                
                // Jika sudah terisi 1 digit, pindah ke kotak berikutnya
                if (input.value.length === 1 && index < 5) {
                    const nextInput = document.querySelectorAll('.otp-input')[index + 1];
                    if (nextInput) {
                        nextInput.focus();
                    }
                }
            },

            handleKeyup(e, index) {
                const input = e.target;
                // Trigger auto-focus jika sudah ada nilai
                if (input.value.length === 1 && index < 5) {
                    const nextInput = document.querySelectorAll('.otp-input')[index + 1];
                    if (nextInput) {
                        nextInput.focus();
                    }
                }
            },

            handleBackspace(e, index) {
                // Jika tekan hapus dan kotak kosong, balik ke kotak sebelumnya
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    const prevInput = document.querySelectorAll('.otp-input')[index - 1];
                    if (prevInput) {
                        prevInput.focus();
                    }
                }
            },

            handlePaste(e) {
                // Ambil data dari clipboard
                const pasteData = e.clipboardData.getData('text').trim();
                
                // Cek apakah data yang di-paste adalah angka dan panjangnya sesuai
                if (!/^\d+$/.test(pasteData)) return;

                const digits = pasteData.split('').slice(0, 6);
                const inputs = document.querySelectorAll('.otp-input');
                
                digits.forEach((digit, i) => {
                    if (inputs[i]) {
                        inputs[i].value = digit;
                    }
                });

                // Fokus ke kotak terakhir yang terisi atau kotak ke-6
                const lastIdx = digits.length >= 6 ? 5 : digits.length - 1;
                if (inputs[lastIdx]) {
                    inputs[lastIdx].focus();
                }
                
                e.preventDefault();
            },

            resendOtp() {
                if(this.seconds === 0) {
                    alert('Kode baru sedang dikirim ke email Anda...');
                    // Di sini tambahkan logic AJAX atau pindah route untuk kirim OTP
                    window.location.reload(); 
                }
            }
        }
    }
</script>

</body>
</html>