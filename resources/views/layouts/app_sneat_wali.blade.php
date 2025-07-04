<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('sneat') }}/assets/" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>{{ config('app.name', 'Laravel') }}</title>

    <meta name="description" content="" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('sneat') }}/assets/img/favicon/favicon.ico" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="{{ asset('sneat') }}/assets/vendor/fonts/boxicons.css" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('sneat') }}/assets/vendor/css/core.css"
        class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('sneat') }}/assets/vendor/css/theme-default.css"
        class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('sneat') }}/assets/css/demo.css" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('sneat') }}/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css" />

    <link rel="stylesheet" href="{{ asset('sneat') }}/assets/vendor/libs/apex-charts/apex-charts.css" />

    <!-- Page CSS -->
    @yield('styles') <!-- Helpers -->
    <script src="{{ asset('sneat') }}/assets/vendor/js/helpers.js"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('sneat') }}/assets/js/config.js"></script>
    <script src="{{ asset('sneat') }}/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="{{ asset('sneat') }}/assets/vendor/libs/popper/popper.js"></script>
    <script src="{{ asset('sneat') }}/assets/vendor/js/bootstrap.js"></script>
    <link rel="stylesheet" href="{{ asset('font/css/all.min.css') }}">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/notifications.css') }}">
    
    <style>
        /* Ensure dropdown menus are visible */
        .dropdown-menu.show {
            display: block !important;
        }
        
        /* Ensure dropdown toggle is clickable */
        .dropdown-toggle {
            cursor: pointer !important;
            pointer-events: auto !important;
        }
        
        /* Ensure badge doesn't block clicks */
        .notifications-badge {
            pointer-events: none !important;
        }
        
        /* Mobile-like dropdown behavior for desktop */
        @media (min-width: 992px) {
            .dropdown-menu {
                position: fixed !important;
                top: 60px !important;
                left: auto !important;
                right: 0 !important;
                width: 100% !important;
                max-width: 320px !important;
                margin: 0 !important;
                border-radius: 0 !important;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
                z-index: 1050 !important;
            }
            
            /* Notifications dropdown */
            .dropdown-notifications .dropdown-menu {
                left: auto !important;
                right: 0 !important;
            }
            
            /* User dropdown */
            .dropdown-user .dropdown-menu {
                left: auto !important;
                right: 0 !important;
            }
        }
        
        /* Force left positioning for all dropdowns */
        .dropdown-menu {
            right: 0 !important;
            left: auto !important;
            transform: none !important;
        }
        
        /* Specific for notifications */
        .dropdown-notifications .dropdown-menu {
            right: 0 !important;
            left: auto !important;
            transform: none !important;
        }
        
        /* Specific for user profile */
        .dropdown-user .dropdown-menu {
            right: 0 !important;
            left: auto !important;
            transform: none !important;
        }
        
        /* Mobile-like overlay effect */
        .dropdown-menu.show {
            animation: slideInDown 0.3s ease-out;
        }
        
        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive adjustments */
        @media (max-width: 991.98px) {
            .dropdown-menu {
                position: absolute !important;
                top: 100% !important;
                left: auto !important;
                right: 0 !important;
                width: auto !important;
                max-width: none !important;
            }
        }
    </style>
    
    <script>
        // Function untuk memuat notifikasi secara dinamis
        function loadNotifications() {
            fetch('/operator/notifications/unread-count')
                .then(response => response.json())
                .then(data => {
                    const badge = document.querySelector('.notifications-badge');
                    if (data.count > 0) {
                        if (badge) {
                            badge.textContent = data.count > 99 ? '99+' : data.count;
                            badge.style.display = 'block';
                        } else {
                            // Buat badge jika belum ada
                            const icon = document.querySelector('.bx-bell');
                            if (icon) {
                                const newBadge = document.createElement('span');
                                newBadge.className = 'badge rounded-pill badge-danger h-px-18 w-px-18 notifications-badge';
                                newBadge.textContent = data.count > 99 ? '99+' : data.count;
                                icon.parentElement.style.position = 'relative';
                                icon.parentElement.appendChild(newBadge);
                            }
                        }
                    } else {
                        if (badge) {
                            badge.style.display = 'none';
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                });
        }
        
        // Load notifications when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadNotifications();
        });
    </script>
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->

            @include('layouts.menu_wali')
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->

                <nav class="layout-navbar container-xxl navbar-detached navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
                    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0   d-xl-none ">
                        <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
                            <i class="icon-base bx bx-menu icon-md"></i>
                        </a>
                    </div>
                    <div class="navbar-nav-right d-flex align-items-center justify-content-end" id="navbar-collapse">
                        <!-- Search -->
                        <div class="navbar-nav align-items-center">
                            <div class="nav-item d-flex align-items-center">
                                <i class="bx bx-search fs-4 lh-0"></i>
                                <input type="text" class="form-control border-0 shadow-none" placeholder="Search..."
                                    aria-label="Search..." />
                            </div>
                        </div>
                        <!-- /Search -->

                        <ul class="navbar-nav flex-row align-items-center ms-md-auto">
                            <!-- Language -->
                            <li class="nav-item dropdown-language dropdown me-2 me-xl-0">
                                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                                    <i class="icon-base bx bx-globe icon-md"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item active" href="javascript:void(0);" data-language="en" data-text-direction="ltr">
                                            <span>English</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);" data-language="fr" data-text-direction="ltr">
                                            <span>French</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);" data-language="ar" data-text-direction="rtl">
                                            <span>Arabic</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="javascript:void(0);" data-language="de" data-text-direction="ltr">
                                            <span>German</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <!--/ Language -->

                            <!-- Style Switcher -->
                            <li class="nav-item dropdown me-2 me-xl-0">
                                <a class="nav-link dropdown-toggle hide-arrow" id="nav-theme" href="javascript:void(0);" data-bs-toggle="dropdown" aria-label="Toggle theme (light)">
                                    <i class="bx-sun icon-base bx icon-md theme-icon-active"></i>
                                    <span class="d-none ms-2" id="nav-theme-text">Toggle theme</span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="nav-theme-text">
                                    <li>
                                        <button type="button" class="dropdown-item align-items-center active" data-bs-theme-value="light" aria-pressed="true">
                                            <span><i class="icon-base bx bx-sun icon-md me-3" data-icon="sun"></i>Light</span>
                                        </button>
                                    </li>
                                    <li>
                                        <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="dark" aria-pressed="false">
                                            <span><i class="icon-base bx bx-moon icon-md me-3" data-icon="moon"></i>Dark</span>
                                        </button>
                                    </li>
                                    <li>
                                        <button type="button" class="dropdown-item align-items-center" data-bs-theme-value="system" aria-pressed="false">
                                            <span><i class="icon-base bx bx-desktop icon-md me-3" data-icon="desktop"></i>System</span>
                                        </button>
                                    </li>
                                </ul>
                            </li>
                            <!-- / Style Switcher-->

                            <!-- Quick links  -->
                            <li class="nav-item dropdown-shortcuts navbar-dropdown dropdown me-2 me-xl-0">
                                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                    <i class="icon-base bx bx-grid-alt icon-md"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end p-0">
                                    <div class="dropdown-menu-header border-bottom">
                                        <div class="dropdown-header d-flex align-items-center py-3">
                                            <h6 class="mb-0 me-auto">Shortcuts</h6>
                                            <a href="javascript:void(0)" class="dropdown-shortcuts-add py-2" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Add shortcuts" data-bs-original-title="Add shortcuts"><i class="icon-base bx bx-plus-circle text-heading"></i></a>
                                        </div>
                                    </div>
                                    <div class="dropdown-shortcuts-list scrollable-container ps">
                                        <div class="row row-bordered overflow-visible g-0">
                                            <div class="dropdown-shortcuts-item col">
                                                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                                    <i class="icon-base bx bx-calendar icon-26px text-heading"></i>
                                                </span>
                                                <a href="app-calendar.html" class="stretched-link">Calendar</a>
                                                <small>Appointments</small>
                                            </div>
                                            <div class="dropdown-shortcuts-item col">
                                                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                                    <i class="icon-base bx bx-food-menu icon-26px text-heading"></i>
                                                </span>
                                                <a href="app-invoice-list.html" class="stretched-link">Invoice App</a>
                                                <small>Manage Accounts</small>
                                            </div>
                                        </div>
                                        <div class="row row-bordered overflow-visible g-0">
                                            <div class="dropdown-shortcuts-item col">
                                                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                                    <i class="icon-base bx bx-user icon-26px text-heading"></i>
                                                </span>
                                                <a href="app-user-list.html" class="stretched-link">User App</a>
                                                <small>Manage Users</small>
                                            </div>
                                            <div class="dropdown-shortcuts-item col">
                                                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                                    <i class="icon-base bx bx-check-shield icon-26px text-heading"></i>
                                                </span>
                                                <a href="app-access-roles.html" class="stretched-link">Role Management</a>
                                                <small>Permission</small>
                                            </div>
                                        </div>
                                        <div class="row row-bordered overflow-visible g-0">
                                            <div class="dropdown-shortcuts-item col">
                                                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                                    <i class="icon-base bx bx-pie-chart-alt-2 icon-26px text-heading"></i>
                                                </span>
                                                <a href="index.html" class="stretched-link">Dashboard</a>
                                                <small>User Dashboard</small>
                                            </div>
                                            <div class="dropdown-shortcuts-item col">
                                                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                                    <i class="icon-base bx bx-cog icon-26px text-heading"></i>
                                                </span>
                                                <a href="pages-account-settings-account.html" class="stretched-link">Setting</a>
                                                <small>Account Settings</small>
                                            </div>
                                        </div>
                                        <div class="row row-bordered overflow-visible g-0">
                                            <div class="dropdown-shortcuts-item col">
                                                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                                    <i class="icon-base bx bx-help-circle icon-26px text-heading"></i>
                                                </span>
                                                <a href="pages-faq.html" class="stretched-link">FAQs</a>
                                                <small>FAQs &amp; Articles</small>
                                            </div>
                                            <div class="dropdown-shortcuts-item col">
                                                <span class="dropdown-shortcuts-icon rounded-circle mb-3">
                                                    <i class="icon-base bx bx-window-open icon-26px text-heading"></i>
                                                </span>
                                                <a href="modal-examples.html" class="stretched-link">Modals</a>
                                                <small>Useful Popups</small>
                                            </div>
                                        </div>
                                    <div class="ps__rail-x" style="left: 0px; bottom: 0px;"><div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div></div><div class="ps__rail-y" style="top: 0px; right: 0px;"><div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 0px;"></div></div></div>
                                </div>
                            </li>
                            <!-- Quick links -->

                            <!-- Notification -->
                            @auth
                                @if(auth()->user()->akses === 'wali')
                                @php
                                    $unreadNotifications = auth()->user()->unreadNotifications()->count();
                                    $totalNotifications = auth()->user()->notifications()->count();
                                @endphp
                                <li class="nav-item dropdown-notifications navbar-dropdown dropdown me-3 me-xl-2">
                                    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                        <span class="position-relative">
                                            <i class="icon-base bx bx-bell icon-md"></i>
                                            @if($unreadNotifications > 0)
                                                <span class="badge rounded-pill bg-danger badge-dot badge-notifications border">{{ $unreadNotifications }}</span>
                                            @endif
                                        </span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end p-0">
                                        <li class="dropdown-menu-header border-bottom">
                                            <div class="dropdown-header d-flex align-items-center py-3">
                                                <h6 class="mb-0 me-auto">Notifikasi</h6>
                                                <div class="d-flex align-items-center h6 mb-0">
                                                    @if($unreadNotifications > 0)
                                                        <span class="badge bg-label-primary me-2">{{ $unreadNotifications }} Baru</span>
                                                        <a href="javascript:void(0)" class="dropdown-notifications-all p-2" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Mark all as read" data-bs-original-title="Mark all as read"><i class="icon-base bx bx-envelope-open text-heading"></i></a>
                                                    @endif
                                                </div>
                                            </div>
                                        </li>
                                        <li class="dropdown-notifications-list scrollable-container ps">
                                            <ul class="list-group list-group-flush">
                                                @forelse(auth()->user()->notifications()->take(5)->get() as $notification)
                                                    <li class="list-group-item list-group-item-action dropdown-notifications-item" data-notification-id="{{ $notification->id }}">
                                                        <div class="d-flex">
                                                            <div class="flex-shrink-0 me-3">
                                                                <div class="avatar">
                                                                    <span class="avatar-initial rounded-circle bg-label-warning">
                                                                        <i class="icon-base bx bx-dollar"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h6 class="small mb-0">
                                                                    {{ $notification->data['title'] ?? 'Pembayaran Baru' }}
                                                                    @if($notification->read_at === null)
                                                                        <span class="badge rounded-pill badge-xs bg-danger ms-1">Baru</span>
                                                                    @endif
                                                                </h6>
                                                                <small class="mb-1 d-block text-body">{{ $notification->data['message'] ?? 'Ada pembayaran baru yang menunggu konfirmasi' }}</small>
                                                                <small class="text-body-secondary">{{ $notification->created_at->locale('id')->diffForHumans() }}</small>
                                                            </div>
                                                            <div class="flex-shrink-0 dropdown-notifications-actions">
                                                                <a href="javascript:void(0)" class="dropdown-notifications-read"><span class="badge badge-dot"></span></a>
                                                                <a href="{{ route('wali.tagihan.index') }}" class="dropdown-notifications-archive"><span class="icon-base bx bx-x"></span></a>
                                                            </div>
                                                        </div>
                                                    </li>
                                                @empty
                                                    <li class="list-group-item list-group-item-action dropdown-notifications-item">
                                                        <div class="d-flex">
                                                            <div class="flex-grow-1 text-center py-3">
                                                                <p class="text-muted mb-0">Tidak ada notifikasi</p>
                                                            </div>
                                                        </div>
                                                    </li>
                                                @endforelse
                                            </ul>
                                        </li>
                                        <li class="border-top">
                                            <div class="d-grid p-4">
                                                <a class="btn btn-primary btn-sm d-flex" href="{{ route('wali.tagihan.index') }}">
                                                    <small class="align-middle">Lihat semua tagihan</small>
                                                </a>
                                            </div>
                                        </li>
                                    </ul>
                                </li>
                                @endif
                            @endauth
                            <!--/ Notification -->
                            <!-- User -->
                            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
                                    <div class="avatar avatar-online">
                                        <img src="{{ asset('sneat') }}/assets/img/avatars/1.png" alt="" class="rounded-circle">
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="avatar avatar-online">
                                                        <img src="{{ asset('sneat') }}/assets/img/avatars/1.png" alt="" class="w-px-40 h-auto rounded-circle">
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-0">{{ auth()->user()->name }}</h6>
                                                    <small class="text-body-secondary">{{ ucfirst(auth()->user()->akses) }}</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider my-1"></div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#"> <i class="icon-base bx bx-user icon-md me-3"></i><span>My Profile</span> </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#"> <i class="icon-base bx bx-cog icon-md me-3"></i><span>Settings</span> </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <span class="d-flex align-items-center align-middle">
                                                <i class="flex-shrink-0 icon-base bx bx-credit-card icon-md me-3"></i><span class="flex-grow-1 align-middle">Billing Plan</span>
                                                <span class="flex-shrink-0 badge rounded-pill bg-danger">4</span>
                                            </span>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider my-1"></div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#"> <i class="icon-base bx bx-dollar icon-md me-3"></i><span>Pricing</span> </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#"> <i class="icon-base bx bx-help-circle icon-md me-3"></i><span>FAQ</span> </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider my-1"></div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('logout') }}"> <i class="icon-base bx bx-power-off icon-md me-3"></i><span>Log Out</span> </a>
                                    </li>
                                </ul>
                            </li>
                            <!--/ User -->
                        </ul>
                    </div>
                </nav>
                <!-- / Navbar -->

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->

                    <div class="container-xxl flex-grow-1 container-p-y">
                        {{-- konten --}}
                        @yield('content')
                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    <footer class="content-footer footer bg-footer-theme">
                        <div
                            class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                            <div class="mb-2 mb-md-0">
                                ©
                                <script>
                                    document.write(new Date().getFullYear());
                                </script>
                                , made with ❤️ by
                                <a href="https://themeselection.com" target="_blank"
                                    class="footer-link fw-bolder">ThemeSelection</a>
                            </div>
                            <div>
                                <a href="https://themeselection.com/license/" class="footer-link me-4"
                                    target="_blank">License</a>
                                <a href="https://themeselection.com/" target="_blank" class="footer-link me-4">More
                                    Themes</a>

                                <a href="https://themeselection.com/demo/sneat-bootstrap-html-admin-template/documentation/"
                                    target="_blank" class="footer-link me-4">Documentation</a>

                                <a href="https://github.com/themeselection/sneat-html-admin-template-free/issues"
                                    target="_blank" class="footer-link me-4">Support</a>
                            </div>
                        </div>
                    </footer>
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->
    <script src="{{ asset('sneat') }}/assets/vendor/libs/jquery/jquery.js"></script>
    <script src="{{ asset('sneat') }}/assets/vendor/libs/popper/popper.js"></script>
    <script src="{{ asset('sneat') }}/assets/vendor/js/bootstrap.js"></script>
    <script src="{{ asset('sneat') }}/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>

    <script src="{{ asset('sneat') }}/assets/vendor/js/menu.js"></script>
    <!-- endbuild -->

    <!-- Vendors JS -->
    <script src="{{ asset('sneat') }}/assets/vendor/libs/apex-charts/apexcharts.js"></script>

    <!-- Main JS -->
    <script src="{{ asset('sneat') }}/assets/js/main.js"></script>

    <!-- Page JS -->
    <script src="{{ asset('sneat') }}/assets/js/dashboards-analytics.js"></script>

    <!-- Select2 JS -->
    <script src="{{ asset('js/select2.min.js') }}"></script>

    <!-- Jquery Mask JS -->
    <script src="{{ asset('js/jquery.mask.min.js') }}"></script>

    <!-- Place this tag in your head or just before your close body tag. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script> <!-- Custom JS -->
    <script>
        $(document).ready(function() {
            $('.rupiah').mask("#.##0", {
                reverse: true
            });
            
            // Simple dropdown functionality
            $('.dropdown-toggle').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var $this = $(this);
                var $dropdownMenu = $this.next('.dropdown-menu');
                
                // Close other dropdowns
                $('.dropdown-menu').not($dropdownMenu).removeClass('show');
                
                // Toggle current dropdown
                $dropdownMenu.toggleClass('show');
                
                // Mobile-like positioning for desktop
                if ($(window).width() >= 992) {
                    if ($this.closest('.dropdown-notifications').length) {
                        // Notifications dropdown - position from right edge
                        $dropdownMenu.css({
                            'position': 'fixed',
                            'top': '60px',
                            'left': 'auto',
                            'right': '0',
                            'width': '320px',
                            'max-width': '320px',
                            'margin': '0',
                            'border-radius': '0',
                            'box-shadow': '0 4px 6px rgba(0, 0, 0, 0.1)',
                            'z-index': '1050'
                        });
                    } else if ($this.closest('.dropdown-user').length) {
                        // User dropdown - position from right edge
                        $dropdownMenu.css({
                            'position': 'fixed',
                            'top': '60px',
                            'left': 'auto',
                            'right': '0',
                            'width': '280px',
                            'max-width': '280px',
                            'margin': '0',
                            'border-radius': '0',
                            'box-shadow': '0 4px 6px rgba(0, 0, 0, 0.1)',
                            'z-index': '1050'
                        });
                    }
                }
                
                console.log('Dropdown clicked:', $this.attr('data-bs-toggle'), 'Menu visible:', $dropdownMenu.hasClass('show'));
            });
            
            // Debug: Log all dropdown elements
            console.log('Found dropdown toggles:', $('.dropdown-toggle').length);
            $('.dropdown-toggle').each(function(index) {
                console.log('Dropdown', index, ':', $(this).attr('class'), 'Menu:', $(this).next('.dropdown-menu').length);
            });
            
            // Test click on dropdown toggles
            $('.dropdown-toggle').on('mouseenter', function() {
                console.log('Mouse entered dropdown:', $(this).attr('class'));
            });
            
            // Test if elements are clickable
            $('.dropdown-toggle').on('mousedown', function() {
                console.log('Mouse down on dropdown:', $(this).attr('class'));
            });
            
            // Close dropdown when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.dropdown').length) {
                    $('.dropdown-menu').removeClass('show');
                }
            });
            
            // Close dropdown when pressing Escape
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape') {
                    $('.dropdown-menu').removeClass('show');
                }
            });
            
            // Handle window resize
            $(window).on('resize', function() {
                // Close all dropdowns when resizing
                $('.dropdown-menu').removeClass('show');
            });
        });
    </script>

    <!-- Notification JS -->
    @auth
        @if(auth()->user()->akses === 'operator')
        <script>
            $(document).ready(function() {
                // Mark notification as read when clicked
                $('.dropdown-notifications-read').on('click', function(e) {
                    e.preventDefault();
                    var notificationItem = $(this).closest('.dropdown-notifications-item');
                    var notificationId = notificationItem.data('notification-id');
                    
                    if (notificationId) {
                        $.ajax({
                            url: '/operator/notifications/' + notificationId + '/mark-as-read',
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    notificationItem.find('.badge-danger').remove();
                                    updateNotificationBadge();
                                }
                            }
                        });
                    }
                });

                // Mark all notifications as read
                $('.dropdown-notifications-all').on('click', function(e) {
                    e.preventDefault();
                    
                    $.ajax({
                        url: '/operator/notifications/mark-all-as-read',
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                $('.notifications-badge').remove();
                                $('.dropdown-notifications-item .badge-danger').remove();
                            }
                        }
                    });
                });

                // Update notification badge count
                function updateNotificationBadge() {
                    $.ajax({
                        url: '/operator/notifications/unread-count',
                        method: 'GET',
                        success: function(response) {
                            var badge = $('.notifications-badge');
                            if (response.count > 0) {
                                if (badge.length === 0) {
                                    $('.dropdown-notifications .nav-link').append(
                                        '<span class="badge rounded-pill badge-danger h-px-18 w-px-18 notifications-badge">' + 
                                        (response.count > 99 ? '99+' : response.count) + '</span>'
                                    );
                                } else {
                                    badge.text(response.count > 99 ? '99+' : response.count);
                                }
                            } else {
                                badge.remove();
                            }
                        }
                    });
                }

                // Auto refresh notifications every 30 seconds
                setInterval(function() {
                    updateNotificationBadge();
                }, 30000);
            });
        </script>
        @endif
    @endauth

    @stack('scripts')
</body>

</html>
