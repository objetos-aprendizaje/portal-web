@if (request()->has('payment_success'))
    @if (filter_var(request()->query('payment_success'), FILTER_VALIDATE_BOOLEAN))
        <div class="bg-[#E7ECF3] py-[12px] px-[27px] rounded-[8px] mb-[15px] text-center">
            <p>Se ha procesado el pago correctamente</p>
        </div>
    @else
        <div class="bg-[#F3E7E7] py-[12px] px-[27px] rounded-[8px] mb-[15px] text-center">
            <p>El pago no se ha procesado correctamente</p>
        </div>
    @endif
@endif
