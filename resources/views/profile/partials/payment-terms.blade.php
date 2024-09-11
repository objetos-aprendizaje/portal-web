<template id="payment-terms">
    <div class="border-t-[1px] lg:border-l-[1px] border-dashed w-full lg:w-auto lg:h-3/4 my-[10px]"></div>

    <!-- desktop -->
    <div class="lg:block hidden">
        <div class="flex flex-wrap justify-between text-color_3 text-[14px] mb-[6px] gap-[6px]">
            <div class="payment-term-name"></div>
            <div class="payment-term-date"></div>
            <div class="font-bold"><span class="payment-term-cost"></span>€</div>
        </div>
        <div>
            <label
                class="paid-term-label rounded-[6px] py-[4px] px-[12px] border border-color_1 text-[14px] text-color_1 font-bold hidden">Pagado</label>
            <label
                class="pay-term-btn border border-[#3EAF3F] text-[#3EAF3F] rounded-[6px] py-[4px] px-[12px] text-[14px] font-bold cursor-pointer hidden basis-auto mt-2 hover:text-white hover:bg-[#3EAF3F]">Pagar</label>
        </div>
    </div>

    <!-- mobile -->
    <div class="lg:hidden block">
        <div class="flex flex-wrap justify-between text-color_3 text-[14px] mb-[6px] gap-[6px]">
            <div class="payment-term-name"></div>
            <div class="payment-term-date-mobile"></div>
        </div>
        <div class="flex justify-between items-center">
            <label
                class="paid-term-label rounded-[6px] py-[4px] px-[12px] border border-color_1 text-[14px] text-color_1 font-bold hidden">Pagado</label>
            <label
                class="pay-term-btn border border-[#3EAF3F] text-[#3EAF3F] rounded-[6px] py-[4px] px-[12px] text-[14px] font-bold cursor-pointer hidden basis-auto hover:text-white hover:bg-[#3EAF3F]">Pagar</label>
            <div class="font-bold"><span class="payment-term-cost"></span>€</div>

        </div>
    </div>

</template>
