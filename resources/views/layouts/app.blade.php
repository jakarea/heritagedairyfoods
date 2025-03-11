<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="heritage dairy foods">
  <meta property="og:title" content="Heritage Dairy Foods">
  <meta property="og:type" content="website">
  <meta property="og:url" content="https://www.heritagedairyfoods.com/">
  <meta property="og:image" content="#">
  <meta name="theme-color" content="#000000">

  <title>Heritage Dairy Foods - Home Page </title>

  <!-- tailwind CSS start -->
  <link rel="shortcut icon" href="/images/favicon.svg" type="image/x-icon">

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