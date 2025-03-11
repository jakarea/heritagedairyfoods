<div>

    @if($product)
    <!-- hero section -->
    <section class="w-full bg-[#EDEFEE] py-14 xl:py-16 relative xl:min-h-[525px]">
        <div class="container">
            <div
                class="flex flex-col xl:flex-row xl:items-center h-full xl:py-10 xl:justify-between gap-y-5 xl:gap-y-0">
                <div class="order-2 text-center relative xl:order-1 xl:text-start xl:min-w-fit">
                    <h1 class="text-lg xl:text-[30px] font-bold text-black xl:leading-[130%] mb-3">বগুড়ার ঐতিহ্য:
                        হেরিটেজ ডেইরি
                        এন্ড ফুড প্রডাক্টস- এর</h1>
                    <h2 class="font-bold text-4xl text-black xl:text-[72px] xl:leading-[100%]">{{ $product['name'] }}
                    </h2>
                    <p class="font-normal text-base xl:text-xl my-4 xl:mb-10 xl:max-w-[70%]">{{ $product['subtitle'] }}
                    </p>
                    <button type="button" onclick="scrollToSection('cart_section')"
                        class="bg-second text-white text-sm font-semibold py-3 px-6 rounded-md anim hover:bg-third xl:text-xl inline-flex items-center gap-x-3 shadow-second">এখনই
                        অর্ডার করুন
                        <img src="/images/icons/arrow-right.svg" alt="arrow">
                    </button>
                </div>
                <div class="order-1 relative xl:order-2 xl:text-end">
                    @if ($product['image'])
                    <img src="{{ url($product['image']) }}" alt="doi" class="mx-auto max-w-[70%] xl:max-w-[85%]">
                    @else
                    <img src="/images/cup-doi.webp" alt="doi" class="mx-auto max-w-[70%] xl:max-w-[85%]">
                    @endif
                </div>
            </div>
        </div>
    </section>
    <!-- hero section -->

    <!-- product pricing -->
    <section class="w-full py-3 xl:py-5">
        <div class="container">
            <div
                class="w-full rounded-lg xl:rounded-[10px] border border-[#EDEFEE] p-3 xl:p-5 grid grid-cols-2 items-center lg:grid-cols-3 xl:grid-cols-4">
                <!-- itm -->
                <div class="text-center p-4 xl:p-5 flex flex-col gap-y-3 xl:gap-y-4">
                    <h5
                        class="bg-[#FAFAFA] text-[#484848] text-base font-medium xl:text-[24px] py-2 xl:py-2.5 rounded-lg">
                        মূল্য
                    </h5>
                    <p class="text-[#484848] text-base font-normal xl:text-[24px]"><span class="font-inter">{{
                            $product['price'] }}</span> ৳
                    </p>
                </div>
                <!-- itm -->
                <!-- itm -->
                <div class="text-center p-4 xl:p-5 flex flex-col gap-y-3 xl:gap-y-4">
                    <h5
                        class="bg-[#FAFAFA] text-[#484848] text-base font-medium xl:text-[24px] py-2 xl:py-2.5 rounded-lg">
                        পরিমাণ
                    </h5>
                    <p class="text-[#484848] text-base font-normal xl:text-[24px]"><span class="font-inter">{{
                            $product['weight'] }}</span>
                        গ্রাম</p>
                </div>
                <!-- itm -->
                <!-- itm -->
                <div class="text-center p-4 xl:p-5 flex flex-col gap-y-3 xl:gap-y-4">
                    <h5
                        class="bg-[#FAFAFA] text-[#484848] text-base font-medium xl:text-[24px] py-2 xl:py-2.5 rounded-lg">
                        মান
                    </h5>
                    <p class="text-[#484848] text-base font-normal xl:text-[24px]">উচ্চ মান</p>
                </div>
                <!-- itm -->
                <!-- itm -->
                <div class="text-center p-4 xl:p-5 flex flex-col gap-y-3 xl:gap-y-4">
                    <h5
                        class="bg-[#FAFAFA] text-[#484848] text-base font-medium xl:text-[24px] py-2 xl:py-2.5 rounded-lg">
                        সার্টিফাইড</h5>
                    <p class="text-[#484848] text-base font-normal xl:text-[24px]">বিএসটিআই </p>
                </div>
                <!-- itm -->
            </div>
        </div>
    </section>
    <!-- product pricing -->

    <!-- cta section start -->
    @include('layouts.partials.top-cta')
    <!-- cta section end -->

    <!-- video section start -->
    <section class="w-full py-5 bg-white xl:py-20">
        <div class="container">
            <div class="text-center mb-5 xl:mb-[30px]">
                <h2 class="text-base xl:text-xl font-normal text-third tracking-[2px]">
                    {{ $product['video']['sub_title'] }}
                </h2>
                <h3 class="text-second text-xl font-semibold xl:text-4xl my-2 xl:mb-8 xl:mt-4">{{
                    $product['video']['title'] }}</h3>
            </div>
            <div class="w-full relative group anim">
                <img src="/images/video-thumb-doi.webp" alt="video thumb"
                    class="w-full h-full object-cover rounded-[10px]">
                <a href="{{ $product['video']['url'] }}"
                    class="absolute left-0 top-0 w-full h-full flex items-center justify-center anim group-hover:scale-125">
                    <img src="/images/icons/play.svg" alt="play" class="max-w-[60px] xl:max-w-[100px]">
                </a>
            </div>
            <div class="text-center mt-5 xl:mt-[30px]">
                <p class="font-normal text-base xl:text-xl mb-3 xl:mb-5">{{ $product['video']['description'] }}
                </p>
                <button type="button" onclick="scrollToSection('cart_section')"
                    class="bg-second text-white text-sm font-semibold py-3 px-6 rounded-md anim hover:bg-third xl:text-xl inline-flex items-center gap-x-3 shadow-second">এখনই
                    অর্ডার করুন
                    <img src="/images/icons/arrow-right.svg" alt="arrow">
                </button>
            </div>
        </div>
    </section>
    <!-- video section end -->

    <!-- product feature section start -->
    <section class="w-full bg-fourth py-10 relative z-20 xl:py-16">
        <img src="/images/pattern-bg-01.webp" alt="pattern bg" class="absolute top-0 left-0 w-full h-full -z-10">
        <div class="container">
            <div class="w-full grid grid-cols-1 gap-y-6 xl:grid-cols-2 xl:items-center xl:gap-x-2">
                <div class="txt order-2 xl:order-1">
                    <h2 class="text-2xl xl:text-[34px] xl:leading-[150%] font-semibold text-second">
                        {{ $product['details'][0]['title'] }}
                    </h2>

                    <ul class="mt-4 xl:mt-5 flex flex-col gap-y-5 xl:divide-y">
                        <li class="flex items-start gap-x-2 xl:gap-x-4">
                            <span
                                class="flex w-5 h-5 bg-second items-center justify-center rounded-full border-[4px] border-[#FFCBCB] shrink-0"></span>
                            <p class="text-base xl:text-xl font-normal text-third">{{
                                $product['details'][0]['description'] }}
                            </p>
                        </li>
                    </ul>

                    <div class="my-7 xl:my-16"></div>

                    @if ($product['details'][0]['title_2'] && $product['details'][0]['description_2'])

                    <h2 class="text-2xl xl:text-[34px] xl:leading-[150%] font-semibold text-second">{{
                        $product['details'][0]['title_2'] }}
                    </h2>

                    <ul class="mt-4 xl:mt-5 flex flex-col gap-y-5 xl:divide-y">
                        <li class="flex items-start gap-x-2 xl:gap-x-4">
                            <span
                                class="flex w-5 h-5 bg-second items-center justify-center rounded-full border-[4px] border-[#FFCBCB] shrink-0"></span>
                            <p class="text-base xl:text-xl font-normal text-third">{{
                                $product['details'][0]['description_2'] }}</p>
                        </li>
                    </ul>
                    @endif
                </div>
                <div class="img order-1 xl:order-2 xl:w-[80%] xl:ml-auto">
                    @if ($product['details'][0]['image'])
                    <img src="{{ url($product['details'][0]['image']) }}" alt="feature-image-03" class="mx-auto w-full">
                    @else
                    <img src="/images/feature-image-03.webp" alt="feature-image-03" class="mx-auto w-full">
                    @endif
                </div>
            </div>
        </div>
    </section>
    <!-- product feature section end -->

    <!-- product feature section start -->
    <section class="w-full bg-white py-10 relative z-20 xl:py-[200px]">
        <div class="container">
            <div class="w-full grid grid-cols-1 gap-y-6 xl:grid-cols-2 xl:items-center xl:gap-x-2">
                <div class="txt order-2">
                    <h2 class="text-2xl xl:text-[34px] xl:leading-[150%] font-semibold text-second">{{
                        $product['details'][1]['title'] }}
                    </h2>

                    <ul class="mt-3 xl:mt-5 flex flex-col gap-y-5">
                        @foreach ($product['details'][1]['lists'] as $listItem)
                        <li class="flex items-center gap-x-2 xl:gap-x-4">
                            <span
                                class="flex w-5 h-5 bg-second items-center justify-center rounded-full border-[4px] border-[#FFCBCB] shrink-0"></span>
                            <p class="text-base xl:text-xl font-normal text-third">
                                {{ $listItem }}
                            </p>
                        </li>
                        @endforeach
                    </ul>

                    <div class="my-5 xl:my-12"></div>

                    @if ($product['details'][1]['title_2'] && $product['details'][1]['description_2'])



                    <h2 class="text-2xl xl:text-[34px] xl:leading-[150%] font-semibold text-second">{{
                        $product['details'][1]['title_2'] }}
                    </h2>

                    <ul class="mt-3 xl:mt-5 flex flex-col gap-y-5">
                        <li class="flex items-center gap-x-2 xl:gap-x-4">
                            <span
                                class="flex w-5 h-5 bg-second items-center justify-center rounded-full border-[4px] border-[#FFCBCB] shrink-0"></span>
                            <p class="text-base xl:text-xl font-normal text-third">{{
                                $product['details'][1]['description_2'] }} </p>
                        </li>
                    </ul>
                    @endif

                </div>
                <div class="img order-1 xl:w-[90%]">
                    @if ($product['details'][1]['image'])
                    <img src="{{ url($product['details'][1]['image']) }}" alt="feature-image-03" class="mx-auto w-full">
                    @else
                    <img src="/images/feature-image-04.webp" alt="feature-image-04" class="mx-auto w-full">
                    @endif
                </div>
            </div>
        </div>
    </section>
    <!-- product feature section end -->

    <!-- purity section start -->
    <section class="w-full bg-fourth py-10 relative z-20 xl:py-16">
        <img src="/images/pattern-bg-01.webp" alt="pattern bg" class="absolute top-0 left-0 w-full h-full -z-10">
        <div class="container">
            <div class="text-center">
                <h2 class="text-second text-xl font-semibold xl:text-[34px] my-2 xl:mb-8 xl:mt-4">পরিশেষে</h2>
                <p
                    class="text-sm md:text-base xl:text-2xl xl:leading-[150%] font-normal text-third text-justify xl:text-center xl:mx-12">
                    {{ $product['conclusion'] }}
                </p>
            </div>
        </div>
    </section>
    <!-- purity section end -->

    <!-- cart section start -->
    <div id="cart_section">
        @livewire('cart')
    </div>
    <!-- cart section end -->

    @else
    @include('layouts.partials.product-not-found')
    @endif


</div>