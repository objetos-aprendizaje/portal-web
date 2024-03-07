<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CarrouselComponent extends Component
{
    public $items;
    public $type;
    /**
     * Create a new component instance.
     */
    public function __construct($items, $type)
    {
        $this->items = $items;
        $this->type = $type;

    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.carrousel-component');
    }
}
