<style>
    /* ===== SIDEBAR BASE (NAVY #003380) ===== */
    #sidebar { 
        width: 260px; 
        height: 100vh; 
        background-color: #003380 !important; 
        position: fixed; 
        left: 0; 
        top: 0; 
        padding: 25px 18px; 
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1100; 
        display: flex; 
        flex-direction: column; 
    }

    /* Efek Menciut (Collapsed) */
    #sidebar.collapsed { width: 85px; }

    /* Sembunyikan teks saat sidebar ciut */
    #sidebar.collapsed .brand-text, 
    #sidebar.collapsed .nav-text, 
    #sidebar.collapsed .menu-title { 
        display: none !important; 
    }

    /* Branding */
    .brand-link { text-decoration: none !important; display: block; margin-bottom: 35px; }
    .brand { display: flex; align-items: center; gap: 12px; padding-left: 5px; }
    
    .logo-circle { 
        width: 45px; height: 45px; background: white; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        color: #003380; flex-shrink: 0; box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }

    /* Menu Title */
    .menu-title {
        font-size: 10px; letter-spacing: 1.5px; color: rgba(255,255,255,0.5) !important;
        margin-top: 20px; margin-bottom: 10px; font-weight: 700;
        text-transform: uppercase; padding-left: 12px;
    }

    /* Link Style */
    .nav-link { 
        color: rgba(255,255,255,0.8) !important; font-weight: 500; 
        padding: 12px 15px; border-radius: 12px; display: flex; 
        align-items: center; text-decoration: none !important; margin-bottom: 5px; 
        transition: all 0.2s;
    }
    .nav-link:hover { background: rgba(255,255,255,0.1); color: white !important; }
    
    /* State Aktif (Warna Putih, Teks Navy) */
    .nav-link.active { 
        background-color: white !important; 
        color: #003380 !important; 
        font-weight: 700; 
        box-shadow: 0 4px 15px rgba(0,0,0,0.2); 
    }

    .nav-icon { width: 25px; text-align: center; font-size: 18px; flex-shrink: 0; }
    .nav-text { margin-left: 12px; font-size: 14px; white-space: nowrap; }

    /* RESPONSIVE HP */
    @media (max-width: 991.98px) {
        #sidebar { left: -260px; }
        #sidebar.active { left: 0 !important; }
    }
</style>

<aside id="sidebar" class="sidebar shadow-lg">
    {{-- LOGO / BRAND --}}
    <a href="{{ route('dashboard') }}" class="brand-link">
        <div class="brand">
            <div class="logo-circle"><i class="fa-solid fa-hard-hat"></i></div>
            <div class="brand-text">
                <h5 class="fw-bold mb-0" style="font-weight: 800; color: white !important;">PTW App</h5>
                <small style="font-size: 10px; opacity: 0.8; display: block; color: white !important;">Safety Management</small>
            </div>
        </div>
    </a>

    <div class="sidebar-menu-container flex-grow-1" style="overflow-y: auto;">
        <nav>
            <div class="menu-title">Main Menu</div>
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-chart-pie"></i></span>
                <span class="nav-text">Dashboard</span>
            </a>

            <div class="menu-title">Management</div>
            
            @php 
                $userRole = trim(strtolower(Auth::user()->role)); 
            @endphp

            {{-- 1. Create PTW (Kontraktor/Maintenance) --}}
            @if(in_array($userRole, ['kontraktor']))
                <a href="{{ route('permits.create') }}" class="nav-link {{ request()->routeIs('permits.create') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="fa-solid fa-plus-circle"></i></span>
                    <span class="nav-text">Request PTW</span>
                </a>
            @endif

            {{-- 2. Edit/Update PDF PTW (Khusus Master/Admin) --}}
            @if(in_array($userRole, ['master', 'admin']))
                <a href="{{ route('superadmin.edit_ptw') }}"
                class="nav-link {{ request()->routeIs('superadmin.edit_ptw') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="fa-solid fa-file-pen"></i></span>
                    <span class="nav-text">Manajemen PTW</span>
                </a>
            @endif

            {{-- 3. Approval (HSE/Safety, Manager, Master) --}}
            @if(in_array($userRole, ['hse/safety']))
                <a href="{{ route('approvals.index') }}" class="nav-link {{ request()->routeIs('approvals.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="fa-solid fa-check-circle"></i></span>
                    <span class="nav-text">Approval Permit</span>
                </a>
            @endif

            {{-- 4. Audit Permit (HSE/Safety, Master) --}}
            @if(in_array($userRole, ['hse/safety']))
                <a href="{{ route('audits.index') }}" class="nav-link {{ request()->routeIs('audits.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="fa-solid fa-clipboard-check"></i></span>
                    <span class="nav-text">Audit Permit</span>
                </a>
            @endif

            {{-- SEKSI KHUSUS MASTER / ADMIN --}}
            @if(in_array($userRole, ['master', 'admin']))
                <div class="menu-title">Administrator</div>
                <a href="{{ route('superadmin.users.index') }}" class="nav-link {{ request()->routeIs('superadmin.users.*') ? 'active' : '' }}">
                    <span class="nav-icon"><i class="fa-solid fa-users-gear"></i></span>
                    <span class="nav-text">Manajemen User</span>
                </a>
            @endif
            
            @if(in_array($userRole, ['hse/safety', 'kontraktor']))
            <div class="menu-title">Reports</div>
            <a href="{{ route('history.history') }}" class="nav-link {{ request()->routeIs('history.*') ? 'active' : '' }}">
                <span class="nav-icon"><i class="fa-solid fa-clock-rotate-left"></i></span>
                <span class="nav-text">History Log</span>
            </a>
            @endif
        </nav>
    </div>
</aside>