<div>
    <section class="w-full py-20 bg-white xl:py-[180px]">
        <div class="container">
            <div class="text-center bg-second py-6 xl:py-7 px-5 rounded-md xl:rounded-[10px]">
                <h2 class="text-xl font-bold xl:text-[40px] text-white xl:leading-[150%]">নিচে আপনার নাম, ঠিকানা আর
                    মোবাইল নম্বর
                    লিখে <br class="hidden xl:block">
                    "অর্ডার করুন" বাটনে ক্লিক করুন</h2>


            </div>

            <div class="mt-10 xl:mt-[100px]">
                <div
                    class="flex flex-col xl:flex-row items-center justify-between border-b border-third border-opacity-20 pb-2 mb-4 xl:pb-4 xl:mb-6">
                    <h3 class="text-xl xl:text-[28px] font-semibold text-black">
                        পছন্দ করুন
                    </h3>
                    <p class="text-sm font-medium text-first xl:text-base">এই রমজানে সারাদেশে হোম ডেলিভারি ফ্রি! <sup
                            class="text-second text-xl">*</sup></p>
                </div>


                <div class="grid grid-cols-1 xl:grid-cols-2 xl:gap-x-[250px] gap-y-3">
                    @foreach ($products as $product)
                    @if ($product['stock'] > 0)
                    <div
                        class="w-full border border-[#EAEAEA] p-3 xl:p-5 grid grid-cols-3 gap-x-3 xl:gap-x-5 items-center xl:flex anim hover:bg-[#F6F6F6] rounded-md">
                        <label for="cart-item-{{ $product['id'] }}"
                            class="img cursor-pointer relative xl:w-[130px] xl:h-[107px] flex justify-center items-center bg-white">
                            <input type="checkbox" name="cart-item-{{ $product['id'] }}"
                                id="cart-item-{{ $product['id'] }}" class="absolute left-2 top-2"
                                wire:change="toggleCart({{ $product['id'] }}, $event.target.checked)"
                                @checked(in_array($product['id'], $isProductInCarts))>

                            <div class="">
                                @if ($product['image'])
                                <img src="{{ url($product['image']) }}" alt="cart" class="max-w-[60px]">
                                @else
                                <img src="/images/products/chini-pata.webp" alt="cart" class="max-w-[60px]">
                                @endif
                            </div>
                        </label>

                        <div class="txt col-span-2">
                            <label for="cart-item-{{ $product['id'] }}"
                                class="text-base block cursor-pointer xl:text-xl font-medium text-black">{{
                                $product['name'] }}</label>

                            <div class="flex items-center justify-start mt-2 gap-x-7 xl:gap-x-10">
                                <div class="max-w-[50%]">
                                    <label for="{{ $product['id'] }}"
                                        class="text-sm xl:text-base font-normal text-black mb-2 xl:mb-2.5">পণ্যের
                                        সংখ্যা</label>
                                    <div class="border border-[#F2F2F2] flex items-center bg-[#EEEEEE]">
                                        <button type="button" wire:click="decrementQuantity({{ $product['id'] }})"
                                            wire:loading.attr="disabled"
                                            wire:target="decrementQuantity({{ $product['id'] }})"
                                            class="w-[30px] text-center h-full xl:w-[60px] xl:text-base relative inline-flex justify-center items-center transition-all duration-150 ease-in-out transform active:scale-75 focus:ring-2 focus:ring-gray-300">

                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                                            </svg>

                                        </button>


                                        <input type="text" id="{{ $product['id'] }}" name="quantity"
                                            class="w-10 xl:w-[60px] h-[26px] xl:h-8 text-sm font-normal text-black xl:text-base text-center bg-white font-inter"
                                            value="{{ $this->getCartItemQuantity($product['id']) }}" readonly>
                                        <button type="button" wire:click="incrementQuantity({{ $product['id'] }})"
                                            wire:loading.attr="disabled"
                                            wire:target="incrementQuantity({{ $product['id'] }})"
                                            class="w-[30px] text-center xl:w-[60px] xl:text-base relative inline-flex justify-center items-center transition-all duration-150 ease-in-out transform active:scale-75 focus:ring-2 focus:ring-gray-300">

                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" class="size-4">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 4.5v15m7.5-7.5h-15" />
                                            </svg>

                                        </button>

                                    </div>
                                </div>
                                <div>
                                    <h5 class="text-sm xl:text-base font-normal text-black mb-2 xl:mb-2.5">মূল্য</h5>
                                    <p class="text-sm xl:text-base font-normal text-black">
                                        <span class="font-inter">{{ $product['offer_price'] ?? $product['price'] }} </span> ৳</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- item -->
                    @endif
                    @endforeach
                </div>
            </div>

            <!-- order details -->
            <form wire:submit.prevent="submit" method="POST"
                class="w-full mt-10 grid grid-cols-1 gap-y-6 xl:mt-20 xl:grid-cols-2 xl:gap-x-8">
                @csrf
                <div class="w-full">
                    <h5 class="text-xl xl:text-[28px] font-semibold text-black">অর্ডার সংক্রান্ত তথ্য</h5>
                    <div class="mt-6 flex flex-col gap-y-5 xl:gap-y-8 xl:mt-24">
                        <div class="w-full">
                            <label for="" class="block w-full text-sm xl:text-lg font-normal text-black mb-2.5">আপনার
                                নাম লিখুন <span class="text-[#F92F2F]">*</span> </label>
                            <input type="text" wire:model="name" placeholder="আপনার নাম লিখুন"
                                class="@error('name') !border-red-500 @enderror common-input">
                            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="w-full">
                            <label for="" class="block w-full text-sm xl:text-lg font-normal text-black mb-2.5">আপনার
                                ঠিকানা*</label>
                            <input type="text" wire:model="address" placeholder="আপনার ঠিকানা"
                                class="@error('address') !border-red-500 @enderror common-input">
                            @error('address') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="w-full">
                            <label for="" class="block w-full text-sm xl:text-lg font-normal text-black mb-2.5">আপনার
                                ফোন
                                নম্বর*</label>
                            <input type="number" wire:model="phone_number" placeholder="আপনার ফোন নম্বর"
                                class="@error('phone_number') !border-red-500 @enderror common-input">
                            @error('phone_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="w-full">
                            <label for="delivery_option"
                                class="block w-full text-sm xl:text-lg font-normal text-black mb-2.5">আমরা কোথায় পৌঁছে
                                দেব?</label>
                            <div class="flex items-center gap-x-4">
                                <div class="flex gap-x-2">
                                    <input type="radio" wire:model="shiping_zone" name="shiping_zone" id="inside_dhaka"
                                        value="inside_dhaka">
                                        {{-- wire:change="shipingType(0)" --}}
                                    <label for="inside_dhaka"
                                        class="text-sm xl:text-lg font-normal text-black block cursor-pointer">ঢাকার
                                        ভেতরে</label>
                                </div>
                                <div class="flex gap-x-2">
                                    <input type="radio" wire:model="shiping_zone" name="shiping_zone" id="outside_dhaka"
                                        value="outside_dhaka">
                                    <label for="outside_dhaka"
                                        class="text-sm xl:text-lg font-normal text-black block cursor-pointer">ঢাকার
                                        বাইরে</label>
                                </div>
                            </div>
                            @error('shiping_zone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-10">
                        @if (session()->has('success'))
                        <div class="bg-green-500 text-white p-4 rounded-md mb-4">
                            {{ session('success') }}
                        </div>
                        @endif
                        @if (session()->has('error'))
                        <div class="bg-red-500 text-white p-4 rounded-md mb-4">
                            {{ session('error') }}
                        </div>
                        @endif
                    </div>
                </div>
                <div class="w-full">
                    <h5 class="text-xl xl:text-[28px] font-semibold text-black">মোট মূল্য</h5>

                    <table class="w-full mt-6 xl:mt-24">
                        <tr>
                            <td>
                                <h5 class="text-base xl:text-lg font-semibold">পণ্য</h5>
                            </td>
                            <td class="text-end pr-5 xl:pr-10">
                                <p class="text-base xl:text-lg font-normal">মোট</p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="py-2">
                                <span class="block w-full border-b border-black border-dotted"></span>
                            </td>
                        </tr>
                        @if (count($cartItems) > 0)

                        @foreach($cartItems as $item)
                        @php
                        $product = collect($products)->firstWhere('id', $item->product_id);
                        @endphp
                        @if($product)
                        <tr>
                            <td class="py-2">
                                <div class="flex items-center gap-x-3 xl:gap-x-5">
                                    <div
                                        class="w-[105px] h-[95px] border border-[#EAEAEA] flex justify-center items-center rounded-md">
                                        @if ($product['image'])
                                        <img src="{{ $product['image'] }}" alt="cart" class="max-w-[65px]">
                                        @else
                                        <img src="/images/products/chini-pata.webp" alt="cart" class="max-w-[65px]">
                                        @endif
                                    </div>
                                    <div>
                                        <h5 class="text-base font-semibold text-black">{{ $product['name'] }}</h5>
                                        <p class="text-sm font-normal text-black">পণ্যের সংখ্যা <span
                                                class="font-inter">{{ $item->quantity }}</span></p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-2 text-end pr-5 xl:pr-10">
                                <p class="text-base xl:text-lg font-normal"><span class="font-inter">{{ $item->price
                                        }}</span> ৳</p>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                        @else
                        <tr>
                            <td colspan="2">
                                <p class="text-sm xl:text-base font-semibold text-red-500 text-center">কার্টে কোন পণ্য
                                    নেই </p>
                            </td>
                        </tr>
                        @endif

                        <tr>
                            <td colspan="2" class="py-2">
                                <span class="block w-full border-b border-black border-dotted"></span>
                            </td>
                        </tr>
                        @php
                        $totalPrice = 0;
                        @endphp
                        @foreach ($cartItems as $item)
                        @php
                        $totalPrice += $item->price;
                        @endphp
                        @endforeach
                        <tr>
                            <td>
                                <h5 class="text-base xl:text-lg font-normal">মোট</h5>
                            </td>
                            <td class="text-end pr-5 xl:pr-10">
                                <p class="text-base xl:text-lg font-normal"><span class="font-inter">{{ $totalPrice
                                        }}</span> ৳</p>
                            </td>
                        </tr>
                        <tr>
                            <td class="pt-5 xl:pt-20">
                                <h5 class="text-base xl:text-lg font-normal">শিপিং</h5>
                            </td>
                            <td class="pt-5 xl:pt-20 text-end pr-5 xl:pr-10">
                                <p class="text-base xl:text-lg font-normal"><span class="font-inter">{{ $shipingValue
                                        }}</span> ৳</p>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="py-2">
                                <span class="block w-full border-b border-black border-dotted"></span>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <h5 class="text-base xl:text-lg font-normal">সর্বমোট</h5>
                            </td>
                            <td class="text-end pr-5 xl:pr-10">
                                <p class="text-base xl:text-lg font-bold"><span class="font-inter">{{ $totalPrice +
                                        $shipingValue }}</span> ৳</p>
                            </td>
                        </tr>
                    </table>

                    <!-- delivery note -->
                    <div class="w-full bg-[#F6F6F6] rounded-md p-5 mt-6 xl:mt-[66px]">
                        <h5 class="text-lg xl:text-xl font-semibold text-black">ক্যাশ অন ডেলিভারি</h5>

                        <div class="w-full bg-[#EAEAEA] py-3 px-2 rounded-[4px] mt-4 xl:text-center relative">
                            <span
                                class="bg-[#EAEAEA] hidden xl:block absolute -top-2 left-6 w-11 h-11 rotate-45"></span>
                            <p class="text-sm xl:text-lg font-normal">কোনো প্রিপেমেন্ট প্রয়োজন নেই – অর্ডার পাওয়ার পরই
                                পেমেন্ট করুন
                            </p>
                        </div>
                    </div>
                    <!-- delivery note -->
                    <div class="text-start">
                        <p class="text-xs xl:text-sm font-normal text-black">আপনার ব্যক্তিগত তথ্য আপনার অর্ডার প্রক্রিয়া
                            করতে, এই ওয়েবসাইটে আপনার ব্যবহার অভিজ্ঞতা উন্নত করতে এবং আমাদের গোপনীয়তা নীতিতে উল্লেখিত
                            অন্যান্য উদ্দেশ্যে ব্যবহার করা হতে পারে !
                        </p>
                    </div>
                    <div class="text-start mt-6">
                        <button type="submit"
                            class="bg-second text-white text-sm font-semibold py-4 px-4 rounded-md anim hover:bg-third w-full block xl:text-4xl"
                            wire:loading.attr="disabled" wire:target="submit">
                            <span wire:loading.remove>অর্ডার করুন</span>
                            <span wire:loading>অর্ডার গ্রহন করা হচ্ছে ...</span>
                        </button>

                    </div>
                </div>
            </form>
            <!-- order details -->
        </div>
    </section>
</div>