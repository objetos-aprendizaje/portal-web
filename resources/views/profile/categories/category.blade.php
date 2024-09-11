@php
    $marginLeft = $level * 34;
    $subcategoryClass = $level > 0 ? 'subcategory' : '';
@endphp

<section>
    <div class="mb-[10px] rounded-[10px] category-selector {{ $subcategoryClass }}"
        style="margin-left: {{ $marginLeft }}px">

        <x-checkbox id="{{ $category['uid'] }}" label="{{ $category['name'] }}" class="categories" gap="12"
            classInput="category-checkbox" :checked="in_array($category['uid'], $user_categories)" />
    </div>

    @if (isset($category['subcategories']) && count($category['subcategories']))
        @foreach ($category['subcategories'] as $subcategory)
            @include('profile.categories.category', ['category' => $subcategory, 'level' => $level + 1])
        @endforeach
    @endif
</section>
