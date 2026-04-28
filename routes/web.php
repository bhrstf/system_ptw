<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

// Import semua Controller
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PermitController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\HistoriesController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Auth\OtpVerifyController;
use App\Http\Controllers\Auth\RegisteredUserController;

// Import Model & Middleware
use App\Models\Permit;
use App\Models\User;
use App\Http\Middleware\IsApprover;

/*
|--------------------------------------------------------------------------
| Web Routes - PTW System Official (FULL VERSION - INTEGRATED PDF EDITOR)
|--------------------------------------------------------------------------
|
| Di sini adalah tempat di mana Anda dapat mendaftarkan rute web untuk aplikasi Anda.
| Rute-rute ini dimuat oleh RouteServiceProvider dalam grup yang berisi middleware "web".
|
*/

// 1. Root mengarah ke Login
Route::get('/', function () {
    return redirect()->route('login');
});

/**
 * --- JALUR REGISTER & OTP ---
 * Mengelola pendaftaran mandiri pengguna dan verifikasi keamanan OTP.
 */
Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::get('/verify-otp', [OtpVerifyController::class, 'show'])->name('otp.verify');
Route::post('/verify-otp', [OtpVerifyController::class, 'verify'])->name('otp.process');

/**
 * --- JALUR LOGIN GOOGLE (SOCIALITE) ---
 * Memungkinkan integrasi login cepat menggunakan akun Google perusahaan.
 */
Route::get('/auth/{provider}', [SocialiteController::class, 'redirect'])->name('social.login');
Route::get('/auth/{provider}/callback', [SocialiteController::class, 'callback']);

/**
 * --- AREA TERPROTEKSI (HARUS LOGIN) ---
 * Seluruh fitur di bawah ini hanya dapat diakses setelah autentikasi berhasil.
 */
Route::middleware(['auth'])->group(function () {
    
    // --- FITUR NOTIFIKASI ---
    // Mengelola status baca notifikasi untuk kenyamanan pengguna.
    Route::post('/notifications/mark-all-read', function () {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user) {
            $user->unreadNotifications->markAsRead();
        }
        return back();
    })->name('markAllRead');

    Route::get('/notifications/all', [NotificationController::class, 'index'])->name('notifications.all');

    // --- JALUR PILIH PERAN (GOOGLE USER) ---
    // Digunakan bagi user baru yang login via Google untuk menentukan departemen.
    Route::get('/pilih-peran', function () {
        return view('auth.pilih-peran'); 
    })->name('pilih-peran');

    Route::post('/simpan-peran', function (Request $request) {
        $request->validate([
            'role' => 'required|in:HSE/Safety,Kontraktor'
        ]);
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user) {
            $user->role = $request->role;
            $user->save();
        }
        return redirect()->route('dashboard');
    })->name('simpan-peran');

    // 2. Halaman Dashboard Utama (Logic Redirect Master vs User Biasa)
    // Master diarahkan ke ringkasan manajerial, user biasa ke operasional.
    Route::get('/dashboard', function () {
        if (Auth::user()->role === 'master') {
            
            // Mengambil data user untuk ditampilkan di dashboard master
            $users = User::where('id', '!=', Auth::id())->get(); 
            
            // Hitung statistik simpel buat widget dashboard administrator
            $stats = [
                'total_users'    => User::count(),
                'total_permit'   => Permit::count(),
                'active_permit'  => Permit::where('status', 'active')->count(),
                'pending_ptw'    => Permit::where('status', 'pending')->count(),
            ];

            return view('superadmin.dashboard', compact('users', 'stats'));
        }

        // Jika bukan master, panggil index PermitController (HSE/Kontraktor)
        return app(PermitController::class)->index();
    })->name('dashboard');

    /**
     * --- 3. GROUP PERMIT TO WORK (STRICT ORDER) ---
     */
    Route::prefix('permit')->name('permits.')->group(function () {
        Route::get('/', [PermitController::class, 'index'])->name('index'); 
        Route::get('/create', [PermitController::class, 'create'])->name('create');
        Route::post('/store', [PermitController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [PermitController::class, 'edit'])->name('edit');
        Route::put('/{id}/update', [PermitController::class, 'update'])->name('update');
        Route::get('/{id}/download', [PermitController::class, 'download'])->name('download');
        Route::get('/{permit}', [PermitController::class, 'show'])->name('show');
        
        // Workflow Routes (Pastikan tidak duplikat)
        Route::patch('/{id}/approve', [PermitController::class, 'approve'])->name('approve');
        Route::patch('/{id}/complete', [PermitController::class, 'complete'])->name('complete');
        Route::patch('/{id}/close', [PermitController::class, 'close'])->name('close');
        Route::patch('/{id}/reject', [PermitController::class, 'reject'])->name('reject');
    });

    /**
     * --- 4. FITUR APPROVAL LIST ---
     * Daftar antrean permit yang menunggu tindakan dari HSE atau Manajer.
     */
    Route::get('/approvals/list', [ApprovalController::class, 'index'])->name('approvals.index');

    /**
     * --- 5. FITUR KHUSUS HSE / APPROVER / PJA ---
     * Fitur sensitif yang dilindungi oleh Middleware IsApprover.
     */
    Route::middleware([IsApprover::class])->group(function () {
        
        Route::patch('/approvals/{id}/update', [ApprovalController::class, 'update'])->name('approvals.update');
        
        Route::post('/approvals/validate-field/{id}', [ApprovalController::class, 'validateField'])->name('approvals.validateField');
        
        Route::get('/audits', [AuditController::class, 'index'])->name('audits.index');
        
        Route::post('/audits', [AuditController::class, 'store'])->name('audits.store');
        
        Route::get('/audits/{id}', [AuditController::class, 'show'])->name('audits.show');
        
        Route::get('/history/export-pdf', [HistoriesController::class, 'exportPDF'])->name('history.export_pdf');
        
        // --- TAMBAHAN BARU: Update Status dari History ---
        Route::patch('/history/{id}/complete', [HistoriesController::class, 'completePermit'])->name('history.complete');
        
    });

    /**
     * --- 6. FITUR KHUSUS MASTER (SUPER ADMIN) ---
     * Bagian ini mengelola manajemen data pengguna dan revisi dokumen final.
     */
    Route::prefix('superadmin')->name('superadmin.')->group(function () {
        
        // --- HALAMAN REVISI PTW (APPROVED ONLY) ---
        // Menampilkan tabel khusus permit yang sudah sah untuk dikelola Superadmin.
        Route::get('/update-ptw', [PermitController::class, 'managementUpdate'])->name('edit_ptw');

        // --- FUNGSI BARU: FORM EDIT IDENTIK (FIX ERROR $permit) ---
        // Ini route untuk membuka form edit yang tampilannya mirip kontraktor
        Route::get('/edit-ptw/{id}', [PermitController::class, 'superadminEdit'])->name('edit_ptw_form');

        // --- PDF EDITOR & KETIK ULANG (INTEGRASI TOTAL) ---
        // Jalur masuk ke mode editor visual untuk mengubah konten PDF asli secara langsung.
        Route::get('/edit-document/{id}/{field}', [PermitController::class, 'editDocument'])->name('edit_document');
        Route::post('/save-document/{id}/{field}', [PermitController::class, 'saveDocument'])->name('save_document');

        // Rute Manajemen User (Menggunakan UserController)
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        
        // Rute untuk proses penghapusan akun oleh admin secara permanen.
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        
    });
    /**
     * --- 7. FITUR PROFILE USER ---
     * Mengelola data pribadi pengguna dan pengaturan akun.
     */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /**
     * --- 8. FITUR HISTORY & ARCHIVE ---
     * Menyimpan seluruh rekam jejak permit yang sudah selesai untuk audit.
     */
    Route::get('/history', [HistoriesController::class, 'index'])->name('history.history');
    Route::get('/history/print/{id}', [HistoriesController::class, 'pdf_ptw'])->name('history.pdf_ptw');

});

require __DIR__.'/auth.php';