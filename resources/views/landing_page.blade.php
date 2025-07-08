<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="keywords" content="Bootstrap, Landing page, Template, Business, Service">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="author" content="Grayrids">
    <title>{{ $title ?? (getInstansiSetting('nama_instansi') ?? "Sistem Pembayaran Sekolah") }} | {{ config('app.name', 'Laravel') }}</title>
    <!--====== Favicon Icon ======-->
    @if(getInstansiLogoStorageUrl())
      <link rel="shortcut icon" href="{{ getInstansiLogoStorageUrl() }}" type="image/png">
    @else
      <link rel="shortcut icon" href="{{asset('slick')}}/img/2.png" type="image/png">
    @endif
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{asset('slick')}}/css/bootstrap.min.css">
    <link rel="stylesheet" href="{{asset('slick')}}/css/animate.css">
    <link rel="stylesheet" href="{{asset('slick')}}/css/LineIcons.css">
    <link rel="stylesheet" href="{{asset('slick')}}/css/owl.carousel.css">
    <link rel="stylesheet" href="{{asset('slick')}}/css/owl.theme.css">
    <link rel="stylesheet" href="{{asset('slick')}}/css/magnific-popup.css">
    <link rel="stylesheet" href="{{asset('slick')}}/css/nivo-lightbox.css">
    <link rel="stylesheet" href="{{asset('slick')}}/css/main.css">    
    <link rel="stylesheet" href="{{asset('slick')}}/css/responsive.css">

  </head>
  
  <body>

    <!-- Header Section Start -->
    <header id="home" class="hero-area">    
      <div class="overlay">
        <span></span>
        <span></span>
      </div>
      <nav class="navbar navbar-expand-md bg-inverse fixed-top scrolling-navbar">
        <div class="container">
          <a href="{{ route('home') }}" class="navbar-brand">
            @php
              $logoUrl = getInstansiLogoUrl();
            @endphp
            @if($logoUrl) 
              <img src="{{ $logoUrl }}" alt="{{ getInstansiSetting('nama_instansi') ?? 'Logo Sekolah' }}" style="max-height: 40px;">
            @endif
            <span class="app-brand-text demo menu-text fw-bolder ms-2">{{ getInstansiSetting('nama_instansi') ?? 'Sistem Pembayaran Sekolah' }}</span>
          </a>       
          <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <i class="lni-menu"></i>
          </button>
          <div class="collapse navbar-collapse" id="navbarCollapse">
            <ul class="navbar-nav mr-auto w-100 justify-content-end">
              {{-- <li class="nav-item">
                <a class="nav-link page-scroll" href="#home">Beranda</a>
              </li>
              <li class="nav-item">
                <a class="nav-link page-scroll" href="#services">Layanan</a>
              </li>  
              <li class="nav-item">
                <a class="nav-link page-scroll" href="#features">Fitur</a>
              </li>                            
              <li class="nav-item">
                <a class="nav-link page-scroll" href="#showcase">Demo</a>
              </li>       
              <li class="nav-item">
                <a class="nav-link page-scroll" href="#contact">Kontak</a>
              </li>  --}}
              <li class="nav-item">
                <a class="btn btn-singin" href="{{ route('login.wali') }}">Login Wali Murid</a>
              </li>
            </ul>
          </div>
        </div>
      </nav>  
      <div class="container">      
        <div class="row space-100">
          <div class="col-lg-6 col-md-12 col-xs-12">
            <div class="contents">
              <h2 class="head-title">{{ getInstansiSetting('nama_instansi') ?? 'Sistem Pembayaran Sekolah' }}</h2>
              <p>Sistem pembayaran SPP yang modern dan terintegrasi untuk memudahkan pengelolaan keuangan sekolah dan pembayaran siswa</p>
              <div class="header-button">
                <a href="{{ route('login.wali') }}" class="btn btn-border-filled">Login Sekarang</a>
                {{-- <a href="#services" class="btn btn-border page-scroll">Pelajari Lebih Lanjut</a> --}}
              </div>
            </div>
          </div>
          <div class="col-lg-6 col-md-12 col-xs-12 p-0">
            <div class="intro-img">
              <img src="{{asset('slick')}}/img/intro.png" alt="">
            </div>            
          </div>
        </div> 
      </div>             
    </header>
    <!-- Header Section End --> 


    <!-- Footer Section Start -->
    <footer>
      <!-- Footer Area Start -->
      <section id="footer-Content">

        <div class="copyright">
          <div class="container">
            <!-- Star Row -->
            <div class="row">
              <div class="col-md-12">
                <div class="site-info text-center">
                  <p>&copy; {{ date('Y') }} {{ getInstansiSetting('nama_instansi') ?? 'Sistem Pembayaran Sekolah' }}. smk.abdinegara798@gmail.com</p>
                  <a href="{{ route('login') }}">Login Operator</a>
                </div>              
                
              </div>
              <!-- End Col -->
            </div>
            <!-- End Row -->
          </div>
        </div>
      <!-- Copyright End -->
      </section>
      <!-- Footer area End -->
      
    </footer>
    <!-- Footer Section End --> 



    <!-- Preloader -->
    <div id="preloader">
      <div class="loader" id="loader-1"></div>
    </div>
    <!-- End Preloader -->

    <!-- jQuery first, then Tether, then Bootstrap JS. -->
    <script src="{{asset('slick')}}/js/jquery-min.js"></script>
    <script src="{{asset('slick')}}/js/popper.min.js"></script>
    <script src="{{asset('slick')}}/js/bootstrap.min.js"></script>
    <script src="{{asset('slick')}}/js/owl.carousel.js"></script>      
    <script src="{{asset('slick')}}/js/jquery.nav.js"></script>    
    <script src="{{asset('slick')}}/js/scrolling-nav.js"></script>    
    <script src="{{asset('slick')}}/js/jquery.easing.min.js"></script>     
    <script src="{{asset('slick')}}/js/nivo-lightbox.js"></script>     
    <script src="{{asset('slick')}}/js/jquery.magnific-popup.min.js"></script>     
    <script src="{{asset('slick')}}/js/form-validator.min.js"></script>
    <script src="{{asset('slick')}}/js/contact-form-script.js"></script>   
    <script src="{{asset('slick')}}/js/main.js"></script>
    
  </body>
</html>