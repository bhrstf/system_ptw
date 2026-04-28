<style>
    /* ============================================================
       TOPBAR BASE STYLE
       ============================================================ */
    .topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #ffffff;
        padding: 12px 25px;
        border-radius: 20px;
        margin-bottom: 35px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 4px 15px rgba(0, 51, 128, 0.03);
    }

    .sidebar-toggle {
        font-size: 24px;
        cursor: pointer;
        color: #003380;
        padding: 8px 12px;
        border-radius: 12px;
        transition: all 0.2s ease-in-out;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        background: transparent;
    }
    .sidebar-toggle:hover { background: #f0f7ff; transform: scale(1.05); }

    .topbar-right { display: flex; align-items: center; gap: 20px; }

    /* ============================================================
       NOTIFICATION DROPDOWN
       ============================================================ */
    .notif-wrapper { position: relative; cursor: pointer; font-size: 20px; color: #64748b; transition: 0.2s; }
    .notif-wrapper:hover { color: #003380; }
    .notif-badge { font-size: 9px; padding: 4px 6px; border: 2px solid #ffffff; font-weight: 800; top: -5px !important; }

    .notification-dropdown {
        width: 350px !important; border: none !important; border-radius: 22px !important;
        box-shadow: 0 15px 50px rgba(0,0,0,0.15) !important; padding: 0 !important;
        overflow: hidden; margin-top: 15px !important; animation: slideDown 0.3s ease-out;
    }

    .noti-header { background: #ffffff; border-bottom: 1px solid #f1f5f9; }
    
    .btn-mark-all {
        font-size: 11px; color: #3b82f6; background: #eff6ff;
        border: none; padding: 5px 12px; border-radius: 8px;
        font-weight: 700; transition: 0.2s; cursor: pointer;
    }
    .btn-mark-all:hover { background: #dbeafe; color: #1d4ed8; }

    .noti-item {
        display: flex; align-items: center; gap: 12px; padding: 15px 20px;
        border-bottom: 1px solid #f8fafc; transition: 0.2s; text-decoration: none !important;
    }
    .noti-item:hover { background: #f8fafc; }
    .noti-item.unread { background: #f1f7ff; } /* Warna beda untuk yang belum dibaca */

    .noti-img-circle {
        width: 42px; height: 42px; border-radius: 50%;
        background: linear-gradient(135deg, #003380 0%, #3b82f6 100%); 
        color: white; display: flex;
        align-items: center; justify-content: center; font-weight: 800; flex-shrink: 0;
        font-size: 14px;
    }

    .noti-text-box { flex-grow: 1; min-width: 0; }
    .noti-username { font-size: 13px; color: #0f172a; display: block; line-height: 1.4; }
    .noti-action { font-size: 12px; color: #64748b; display: block; margin-bottom: 2px; }
    .noti-time { font-size: 10px; color: #94a3b8; }

    /* ============================================================
       PROFILE PILL & DROPDOWN
       ============================================================ */
    .profile-pill { 
        display: flex; align-items: center; gap: 15px; 
        background: #f8fafc; padding: 6px 8px 6px 18px; 
        border-radius: 50px; border: 1px solid #f1f5f9; 
        transition: all 0.3s ease; cursor: pointer; 
    }
    .profile-pill:hover { background: #ffffff; box-shadow: 0 4px 12px rgba(0, 51, 128, 0.08); border-color: #e2e8f0; }

    .user-meta { display: flex; flex-direction: column; justify-content: center; line-height: 1.2; text-align: right; }
    .user-meta b { font-size: 13px; color: #001533; font-weight: 700; display: block; }
    .user-role-text { font-size: 10px; color: #64748b; font-weight: 600; text-transform: capitalize; }

    .avatar-navy-circle { 
        width: 36px; height: 36px; border-radius: 50%; 
        box-shadow: 0 2px 8px rgba(0, 51, 128, 0.2); flex-shrink: 0; 
        object-fit: cover;
    }

    .profile-dropdown {
        width: 280px !important; border: none !important; border-radius: 24px !important;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15) !important; padding: 0 !important;
        overflow: hidden; margin-top: 15px !important; animation: slideDown 0.3s ease-out;
    }
    .profile-header-bg { background: linear-gradient(135deg, #003380 0%, #3b82f6 100%); height: 80px; width: 100%; }
    .profile-body { padding: 0 20px 20px 20px; text-align: center; margin-top: -45px; }
    
    .avatar-large { 
        width: 80px; height: 80px; border-radius: 50%; border: 4px solid #ffffff;
        background: #ffffff; margin: 0 auto 12px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1); object-fit: cover;
    }

    .user-name-dropdown { font-weight: 700; color: #001533; font-size: 15px; margin-bottom: 2px; }
    .user-role-badge { 
        display: inline-block; padding: 3px 10px; background: #eef2ff; 
        color: #4f46e5; border-radius: 20px; font-size: 9px; 
        font-weight: 700; text-transform: uppercase; margin-bottom: 12px;
    }

    .profile-footer { display: flex; padding: 12px; gap: 10px; background: #f8fafc; border-top: 1px solid #edf2f7; }
    .btn-dropdown-profile { flex: 1; padding: 10px; border-radius: 12px; font-size: 11px; font-weight: 700; text-decoration: none; transition: 0.3s; text-align: center; border: none; }
    .btn-view-profile { background: white; color: #64748b; border: 1px solid #e2e8f0; }
    .btn-view-profile:hover { background: #f8fafc; }
    .btn-signout { background: #fee2e2; color: #ef4444; }
    .btn-signout:hover { background: #fecaca; }

    @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="topbar">
    <button type="button" class="sidebar-toggle" id="sidebarToggle">
        <i class="fa-solid fa-bars"></i>
    </button>

    <div class="topbar-right">
        <div class="dropdown">
            @php 
                $unreadNotifications = Auth::user()->unreadNotifications;
                $unreadCount = $unreadNotifications->count();
                $allNotifications = Auth::user()->notifications()->latest()->take(10)->get();
            @endphp
            
            <div class="notif-wrapper" data-bs-toggle="dropdown">
                <i class="fa-regular fa-bell"></i>
                @if($unreadCount > 0)
                    <span class="position-absolute translate-middle badge rounded-pill bg-danger notif-badge">
                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                    </span>
                @endif
            </div>

            <ul class="dropdown-menu dropdown-menu-end notification-dropdown">
                <div class="noti-header d-flex justify-content-between align-items-center p-3">
                    <h6 class="mb-0 fw-bold" style="font-size: 14px; color: #001533;">Notifications</h6>
                    @if($unreadCount > 0)
                        <form action="{{ route('markAllRead') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn-mark-all">Tandai sudah dibaca</button>
                        </form>
                    @endif
                </div>

                <div style="max-height: 380px; overflow-y: auto;">
                    @forelse($allNotifications as $n)
                        <li>
                            <a class="noti-item {{ $n->read_at ? '' : 'unread' }}" href="{{ $n->data['url'] ?? '#' }}">
                                <div class="noti-img-circle">
                                    {{ strtoupper(substr($n->data['applicant_name'] ?? 'S', 0, 1)) }}
                                </div>
                                <div class="noti-text-box">
                                    <span class="noti-username fw-bold">{{ $n->data['applicant_name'] ?? 'System' }}</span>
                                    <span class="noti-action">
                                        {{ $n->data['message'] ?? 'Ada permit baru di ' . ($n->data['location'] ?? 'Lokasi') }}
                                    </span>
                                    <span class="noti-time">
                                        <i class="fa-regular fa-clock me-1"></i>{{ $n->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                @if(!$n->read_at)
                                    <div style="width: 8px; height: 8px; background: #3b82f6; border-radius: 50%;"></div>
                                @endif
                            </a>
                        </li>
                    @empty
                        <div class="p-5 text-center">
                            <i class="fa-regular fa-bell-slash d-block mb-2 text-muted" style="font-size: 24px; opacity: 0.3;"></i>
                            <p class="text-muted small mb-0">Belum ada notifikasi</p>
                        </div>
                    @endforelse
                </div>

                <div class="p-2 text-center border-top">
                    <a href="{{ route('notifications.all') }}" class="fw-bold text-primary small" style="text-decoration:none; font-size: 11px;">
                        Lihat Semua Aktivitas
                    </a>
                </div>
            </ul>
        </div>

        <div class="dropdown">
            <div class="profile-pill" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="user-meta d-none d-md-flex">
                    <b>{{ Auth::user()->name }}</b>
                    <span class="user-role-text">{{ Auth::user()->role ?? 'User' }}</span>
                </div>
                <img src="{{ Auth::user()->profile_photo_url }}" class="avatar-navy-circle shadow-sm">
            </div>

            <div class="dropdown-menu dropdown-menu-end profile-dropdown">
                <div class="profile-header-bg"></div>
                <div class="profile-body">
                    <img src="{{ Auth::user()->profile_photo_url }}" class="avatar-large">
                    <div class="user-name-dropdown">{{ Auth::user()->name }}</div>
                    <div class="user-role-badge">{{ Auth::user()->role ?? 'General User' }}</div>
                    
                    <div class="text-muted small mb-1" style="font-size: 11px;">
                        <i class="fa-regular fa-envelope me-1"></i> {{ Auth::user()->email }}
                    </div>
                    <div class="text-muted small" style="font-size: 11px;">
                        <i class="fa-solid fa-building me-1"></i> 
                        {{ Auth::user()->company ?? 'Perusahaan belum diatur' }}
                    </div>
                </div>

                <div class="profile-footer">
                    <a href="{{ route('profile.edit') }}" class="btn-dropdown-profile btn-view-profile">Edit Profile</a>
                    <form method="POST" action="{{ route('logout') }}" class="flex-grow-1">
                        @csrf
                        <button type="submit" class="btn-dropdown-profile btn-signout w-100">Sign Out</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
