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

            @include('layouts.menu')
            <!-- / Menu -->

            <!-- Layout container -->
            <div class="layout-page">
                <!-- Navbar -->

                <nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
                    id="layout-navbar">
                    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
                            <i class="bx bx-menu bx-sm"></i>
                        </a>
                    </div>

                    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                        <!-- Search -->
                        <div class="navbar-nav align-items-center">
                            <div class="nav-item d-flex align-items-center">
                                <i class="bx bx-search fs-4 lh-0"></i>
                                <input type="text" class="form-control border-0 shadow-none" placeholder="Search..."
                                    aria-label="Search..." />
                            </div>
                        </div>
                        <!-- /Search -->

                        <ul class="navbar-nav flex-row align-items-center ms-auto">
                            <!-- Place this tag where you want the button to render. -->
                            <li class="nav-item lh-1 me-3">
                                <a class="github-button"
                                    href="https://github.com/themeselection/sneat-html-admin-template-free"
                                    data-icon="octicon-star" data-size="large" data-show-count="true"
                                    aria-label="Star themeselection/sneat-html-admin-template-free on GitHub">Star</a>
                            </li>

                            <!-- Notifications -->
                            @auth
                                @if(auth()->user()->akses === 'operator')
                                @php
                                    $unreadNotifications = auth()->user()->unreadNotifications()->count();
                                    $totalNotifications = auth()->user()->notifications()->count();
                                    $user = auth()->user();
                                @endphp
                                <!-- Debug: User {{ $user->name }} ({{ $user->akses }}) - {{ $unreadNotifications }} unread, {{ $totalNotifications }} total -->
                                <li class="nav-item navbar-dropdown dropdown-notifications dropdown me-3">
                                    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                                        data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" style="position: relative;">
                                        <i class="bx bx-bell bx-sm"></i>
                                        @if($unreadNotifications > 0)
                                            <span class="badge rounded-pill badge-danger" 
                                                  style="position: absolute; top: -8px; right: -8px; font-size: 10px; min-width: 18px; height: 18px; line-height: 18px; text-align: center; z-index: 1; pointer-events: none;">
                                                {{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}
                                            </span>
                                        @else
                                            <!-- Debug: No unread notifications -->
                                        @endif
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end py-0">
                                        <li class="dropdown-menu-header border-bottom">
                                            <div class="dropdown-header d-flex align-items-center py-3">
                                                <h5 class="text-body mb-0 me-auto">Notifikasi</h5>
                                                @if($unreadNotifications > 0)
                                                    <a href="javascript:void(0)" class="dropdown-notifications-all text-body"
                                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                                        title="Mark all as read">
                                                        <i class="bx fs-4 bx-envelope-open"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </li>
                                        <li class="dropdown-notifications-list scrollable-container">
                                            <ul class="list-group list-group-flush">
                                                @forelse(auth()->user()->notifications()->take(5)->get() as $notification)
                                                    <li class="list-group-item list-group-item-action dropdown-notifications-item" data-notification-id="{{ $notification->id }}">
                                                        <div class="d-flex">
                                                            <div class="flex-shrink-0 me-3">
                                                                <div class="avatar">
                                                                    <span class="avatar-initial rounded bg-label-warning">
                                                                        <i class="bx bx-dollar"></i>
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="flex-grow-1">
                                                                <h6 class="msg-name">
                                                                    {{ $notification->data['title'] ?? 'Pembayaran Baru' }}
                                                                    @if($notification->read_at === null)
                                                                        <span class="badge rounded-pill badge-xs bg-danger ms-1">Baru</span>
                                                                    @endif
                                                                </h6>
                                                                <p class="msg-body">{{ $notification->data['message'] ?? 'Ada pembayaran baru yang menunggu konfirmasi' }}</p>
                                                                <p class="msg-time">{{ $notification->created_at->diffForHumans() }}</p>
                                                            </div>
                                                            <div class="flex-shrink-0 dropdown-notifications-actions">
                                                                <a href="javascript:void(0)" class="dropdown-notifications-read">
                                                                    <span class="badge badge-dot"></span>
                                                                </a>
                                                                <div class="dropdown-notifications-actions">
                                                                    <a href="{{ route('pembayaran.index') }}" class="dropdown-notifications-archive">
                                                                        <span class="bx bx-archive"></span>
                                                                    </a>
                                                                </div>
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
                                        <li class="dropdown-menu-footer border-top">
                                            <a href="{{ route('pembayaran.index') }}" class="dropdown-item d-flex justify-content-center p-3">
                                                Lihat semua pembayaran
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                @endif
                            @endauth

                            <!-- User -->
                            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                                    data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                    <div class="avatar avatar-online">
                                        <img src="{{ asset('sneat') }}/assets/img/avatars/1.png" alt
                                            class="w-px-40 h-auto rounded-circle" />
                                    </div>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <div class="d-flex">
                                                <div class="flex-shrink-0 me-3">
                                                    <div class="avatar avatar-online">
                                                        <img src="{{ asset('sneat') }}/assets/img/avatars/1.png" alt
                                                            class="w-px-40 h-auto rounded-circle" />
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <span class="fw-semibold d-block">{{ auth()->user()->name }}</span>
                                                    <small class="text-muted">{{ ucfirst(auth()->user()->akses) }}</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <i class="bx bx-user me-2"></i>
                                            <span class="align-middle">My Profile</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <i class="bx bx-cog me-2"></i>
                                            <span class="align-middle">Settings</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="#">
                                            <span class="d-flex align-items-center align-middle">
                                                <i class="flex-shrink-0 bx bx-credit-card me-2"></i>
                                                <span class="flex-grow-1 align-middle">Billing</span>
                                                <span
                                                    class="flex-shrink-0 badge badge-center rounded-pill bg-danger w-px-20 h-px-20">4</span>
                                            </span>
                                        </a>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('logout') }}">
                                            <i class="bx bx-power-off me-2"></i>
                                            <span class="align-middle">Log Out</span>
                                        </a>
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
