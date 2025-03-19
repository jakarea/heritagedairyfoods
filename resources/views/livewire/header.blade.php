<div>
    {{-- home delivery free --}}
    <div class="w-full bg-five py-1 fixed top-0 left-0 z-[999]">
        <p class="font-semibold text-sm xl:text-lg text-center text-first">আসন্ন ঈদ উপলক্ষে সারাদেশে ফ্রি হোম ডেলিভারি!</p>
    </div>
    {{-- home delivery free --}}

    @if (session()->has('success'))
    <div class="w-full bg-green-600 py-2 fixed top-0 left-0 z-[9999]">
        <div class="container text-center">
            <div class="text-white text-sm xl:text-base font-semibold">
                {{ session('success') }}
            </div>
        </div>
    </div>
    @endif 

    <header class="header-sec py-3 xl:py-3.5 mt-7 xl:mt-9">
        <div class="container">
            <div class="flex justify-between items-center flex-cosl xl:flex-row">
                <div class="left">
                    <a href="{{ url('/') }}">
                        <img src="/images/logo.svg" alt="Logo" class="mr-auto max-w-[200px] xl:max-w-[390px]">
                    </a>
                </div>
                <div class="right">
                    @include('layouts.partials.whatsapp-button')
                </div>
            </div>
        </div>
    </header>
</div>