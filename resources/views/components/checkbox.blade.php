<div class="relative checkbox-container  {{ $class }}">
    <input type="checkbox" id="{{ $id }}" class="hidden custom-checkbox {{ $classInput }}"
        {{ $checked ? 'checked' : '' }} value="{{ $id }}" />
    <button for="{{ $id }}" class="cursor-pointer flex items-center gap-[{{ $gap }}px]">
        <div class="checkbox-icon {{ $checked ? 'checked' : '' }}">
            <svg class="{{ $checked ? '' : 'hidden' }} checkmark rounded-[3px]" xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                    clip-rule="evenodd" />
            </svg>
        </div>
        <label for="{{ $id }}" class="{{ $classLabel }} cursor-pointer">{{ $label }}</label>
    </button>
</div>
