<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="index.html" class="app-brand-link">
            <span class="app-brand-logo demo">
                @php
                    $logoUrl = getInstansiLogoUrl();
                @endphp
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="Logo Sekolah" style="max-height: 50px;">
                @endif
            </span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2">SPP SMK AN</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-item {{ request()->is('operator/beranda') ? 'active' : '' }}">
            <a href="{{ route('operator.beranda') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-home-circle"></i>
                <div data-i18n="Analytics">Beranda</div>
            </a>
        </li>

        <!-- Setting -->
        <li class="menu-item {{ request()->is('operator/setting*') ? 'active' : '' }}">
            <a href="{{ route('setting.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-cog"></i>
                <div data-i18n="Analytics">Pengaturan</div>
            </a>
        </li>

        <!-- Data Master -->
        <li class="menu-item {{ request()->is('operator/user*') || request()->is('operator/wali*') || request()->is('operator/siswa*') || request()->is('operator/jurusan*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-data"></i>
                <div data-i18n="Layouts">Data Master</div>
                <div class="badge bg-label-primary rounded-pill ms-auto">4</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->is('operator/user*') ? 'active' : '' }}">
                    <a href="{{ route('user.index') }}" class="menu-link">
                        <div data-i18n="Without menu">Data User</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->is('operator/wali*') ? 'active' : '' }}">
                    <a href="{{ route('wali.index') }}" class="menu-link">
                        <div data-i18n="Without navbar">Data Wali Murid</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->is('operator/siswa*') ? 'active' : '' }}">
                    <a href="{{ route('siswa.index') }}" class="menu-link">
                        <div data-i18n="Container">Data Siswa</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->is('operator/jurusan*') ? 'active' : '' }}">
                    <a href="{{ route('jurusan.index') }}" class="menu-link">
                        <div data-i18n="Fluid">Data Jurusan</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Rekening Sekolah -->
        <li class="menu-item {{ request()->is('operator/bank-sekolah*') ? 'active' : '' }}">
            <a href="{{ route('bank-sekolah.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-money"></i>
                <div data-i18n="Basic">Rekening Sekolah</div>
            </a>
        </li>

        
        <!-- Biaya -->
        <li class="menu-item {{ request()->is('operator/biaya*') ? 'active' : '' }}">
            <a href="{{ route('biaya.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-money"></i>
                <div data-i18n="Basic">Data Biaya</div>
            </a>
        </li>

        <!-- Tagihan -->
        <li class="menu-item {{ request()->is('operator/tagihan*') ? 'active' : '' }}">
            <a href="{{ route('tagihan.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-money"></i>
                <div data-i18n="Basic">Data Tagihan</div>
            </a>
        </li>

        <!-- Pembayaran -->
        <li class="menu-item {{ request()->is('operator/pembayaran*') ? 'active' : '' }}">
            <a href="{{ route('pembayaran.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-credit-card"></i>
                <div data-i18n="Basic">Data Pembayaran</div>
            </a>
        </li>

        <!-- Laporan -->
        <li class="menu-item {{ request()->is('operator/laporanform*') ? 'active' : '' }}">
            <a href="{{ route('laporanform.create') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-file"></i>
                <div data-i18n="Basic">Laporan</div>
            </a>
        </li>

        <!-- Notifications -->
        <li class="menu-item {{ request()->is('operator/notifications*') ? 'active' : '' }}">
            <a href="{{ route('notifications.index') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-bell"></i>
                <div data-i18n="Basic">Notifikasi</div>
                @php
                    $unreadCount = auth()->user()->unreadNotifications()->count();
                @endphp
                @if($unreadCount > 0)
                    <span class="badge rounded-pill bg-danger badge-dot badge-notifications border ms-auto"></span>
                @endif
            </a>
        </li>


        <li class="menu-item {{ request()->is('operator/whatsapp*') ? 'active' : '' }}">
            <a href="{{ route('whatsapp.settings') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-phone"></i>
                <div data-i18n="WhatsApp">Pengaturan WhatsApp</div>
            </a>
        </li>

        <li class="menu-item">
            <a href="{{ route('logout') }}" class="menu-link">
                <i class="menu-icon tf-icons bx bx-log-out"></i>
                <div data-i18n="Basic">Logout</div>
            </a>
        </li>
        <!-- User interface -->

    </ul>
</aside>
