<div>
    @if (session()->has('success'))
    <div class="w-full bg-green-600 py-2 xl:py-3">
        <div class="container text-center">
            <div class="text-white text-sm xl:text-base font-semibold">
                {{ session('success') }}
            </div>
        </div>
    </div>
    @endif

    <header class="header-sec py-3 xl:py-3.5">
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