<section class="w-full py-10 xl:py-[50px]">
    <div class="container">
        <div class="w-full grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-y-4 xl:gap-x-8">
            @foreach ($products as $product)
            <!-- item -->
            <div class="w-full bg-[#FCFCFC] border border-[#DFDFDF] p-5 rounded-[4px]">
                <div class="bg-white text-center p-4 min-h-[225px] xl:h-[225px] flex justify-center items-center">
                    @if ($product['image'])
                    <img src="{{ $product['image'] }}" alt="doi"
                        class="w-full max-w-[50%] {{ $product['type'] == 'small' ? 'xl:max-w-[60%]' : 'xl:max-w-[80%]' }} object-contain">
                    @else
                    <img src="/images/products/chini-pata.webp" alt="doi"
                        class="w-full max-w-[50%] xl:max-w-[60%] object-contain">
                    @endif
                </div>
                <div class="text-center mb-5 mt-4 xl:mt-5">
                    <h2 class="font-medium text-base xl:text-lg">{{ $product['name'] }}</h2>
                    <h3
                        class="xl:mb-10 mb-6 text-base xl:text-xl font-normal text-first mt-3 xl:mt-5 flex items-center gap-x-4 xl:gap-x-5 justify-center">
                        <div><span class="font-inter">{{ $product['weight'] }}</span> গ্রাম </div>
                        <div><span class="font-inter">{{ $product['price'] }}</span> ৳</div>
                    </h3>
                    <div class="flex items-center justify-between gap-x-4 xl:gap-x-[23px]">
                        <a href="{{ url('product/'.$product['slug']) }}"
                            class="text-base h-12 xl:text-xl group font-normal text-[#B11116] flex items-center justify-center py-2 xl:py-3 px-8 gap-x-2 border border-[#E9E9E9] anim hover:bg-second hover:text-white w-full">বিস্তারিত
                            দেখুন
                            <svg width="24" class="text-[#B11116] group-hover:text-white" height="24"
                                viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M13.5 4.5L21 12M21 12L13.5 19.5M21 12H3" stroke="currentColor"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a> 

                        <button type="button" wire:click="addProductToCart({{ $product['id'] }})"
                            class="flex items-center justify-center py-1 xl:py-3 px-4 w-[109px] gap-x-2 rounded-sm bg-second h-12 anim hover:bg-third">
                            <img src="/images/icons/shopping-cart.svg" alt="cart">
                        </button>

                    </div>
                </div>
            </div>
            <!-- item -->
            @endforeach 


        </div>
    </div>
</section>