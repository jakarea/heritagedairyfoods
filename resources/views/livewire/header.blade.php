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
                    <a target="_blank" href="https://api.whatsapp.com/send?phone=8801711728630" aria-label='whatsapp'
                        class="bg-second text-white text-sm font-semibold py-2 px-4 rounded-md anim hover:bg-third xl:text-xl xl:py-2.5 xl:px-6 inline-flex items-center gap-x-2">
                        <img src="/images/icons/whatsapp-icon.svg" alt="arrow" class="hidden xl:inline w-6 text-white fill-white"> হোয়াটস্যাপ করুন
                    </a>
                </div>
            </div>
        </div>
    </header>
</div>