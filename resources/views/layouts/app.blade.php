<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- General Meta Tags -->
  <title>Heritage Dairy Foods - Pure and Traditional Dairy Products</title>
  <meta name="description"
    content="Heritage Dairy Foods offers pure, natural, and chemical-free dairy products including traditional Bogura Doi, Ghee, and Premium Lachha Semai, directly from our farms to your table.">
  <meta name="keywords"
    content="Traditional Dairy Products, Pure Ghee, Bogura Doi, Lachha Semai, Probiotic Dairy, Healthy Dairy Products, Chemical-Free Dairy, Farm Fresh Dairy">
  <meta name="author" content="Heritage Dairy Foods">
  <link rel="canonical" href="https://heritagedairyfoods.com/">

  <!-- Open Graph Meta Tags -->
  <meta property="og:title" content="Heritage Dairy Foods - Pure and Traditional Dairy Products">
  <meta property="og:locale" content="en_US">
  <meta property="og:type" content="website">
  <meta property="og:description"
    content="Heritage Dairy Foods offers pure, natural, and chemical-free dairy products including traditional Bogura Doi, Ghee, and Premium Lachha Semai, directly from our farms to your table.">
  <meta property="og:url" content="https://heritagedairyfoods.com/">
  <meta property="og:site_name" content="Heritage Dairy Foods">
  <meta property="og:image" content="{{ asset('images/home-image.png') }}">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:image:type" content="image/webp">

  <!-- Twitter Meta Tags -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Heritage Dairy Foods - Pure and Traditional Dairy Products">
  <meta name="twitter:description"
    content="Heritage Dairy Foods offers pure, natural, and chemical-free dairy products including traditional Bogura Doi, Ghee, and Premium Lachha Semai, directly from our farms to your table.">
  <meta name="twitter:image" content="{{ asset('images/home-image.png') }}">
  <meta name="twitter:site" content="@HeritageDairyFoods">
  <meta name="twitter:creator" content="@HeritageDairyFoods">

  <!-- tailwind CSS start -->
  <link rel="shortcut icon" href="{{ asset('images/favicon.png') }}" type="image/x-icon">

  <!-- swipper slider css -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <!-- swipper slider css -->

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
    rel="stylesheet">

  <!-- Styles / Scripts -->
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @livewireStyles
</head>

{{-- {/* Facebook Pixel Tracking Script */} --}}
<script id="facebook-pixel" strategy="afterInteractive">
  {`
          !function(f,b,e,v,n,t,s)
          {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
          n.callMethod.apply(n,arguments):n.queue.push(arguments)};
          if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
          n.queue=[];t=b.createElement(e);t.async=!0;
          t.src=v;s=b.getElementsByTagName(e)[0];
          s.parentNode.insertBefore(t,s)}(window, document,'script',
          'https://connect.facebook.net/en_US/fbevents.js');
          fbq('init', 'PIXEL_ID');
          fbq('track', 'PageView');
        `}
</script>

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-D2LF7C4Y59"></script>
<script>
  window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());

gtag('config', 'G-D2LF7C4Y59');
</script>

<body class="anim">


  <!-- header section start -->
  @livewire('header')
  <!-- header section end -->

  @if ($component == 'product-details-page')
  @livewire($component, ['productSlug' => $productSlug])
  @else
  @livewire($component)
  @endif

  <!-- footer and cta section start -->
  @livewire('footer')
  <!-- footer and cta section end -->



  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

  <script>
    document.addEventListener("DOMContentLoaded", function () {
          new Swiper(".heroBannerSwiper", {
            slidesPerView: 1,
            spaceBetween: 0,
            loop: true,
            effect: "fade", // Enable fade effect
            fadeEffect: {
              crossFade: true, // Smooth fade transition
            },
            autoplay: {
              delay: 4000,
              disableOnInteraction: false,
            },
            speed: 2000, // Adjust transition speed for smoothness
            pagination: {
              el: ".swiper-pagination",
              clickable: true, // Allows users to click on dots to navigate
            },
            breakpoints: {
              290: { slidesPerView: 1 },
              768: { slidesPerView: 1 },
              1024: { slidesPerView: 1 },
              1320: { slidesPerView: 1 },
            },
          });
    
          new Swiper(".reviewSwiper", {
            slidesPerView: 2,
            spaceBetween: 30,
            loop: true,
            autoplay: {
              delay: 3000,
              disableOnInteraction: false,
            },
            navigation: {
              nextEl: ".swiper-button-nexts",
              prevEl: ".swiper-button-prevs",
            },
            breakpoints: {
              290: { slidesPerView: 1 },
              768: { slidesPerView: 1 },
              1024: { slidesPerView: 2 },
              1320: { slidesPerView: 2 },
            }
          });
    
    
        }); 
  </script>

  <script>
    function scrollToSection(sectionId) {
  const element = document.getElementById(sectionId);
  if (element) {
      window.scrollTo({
          top: element.offsetTop,
          behavior: 'smooth',
      });
  }
}
  </script>

  @livewireScripts
</body>

</html>