<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Checkbox extends Component
{
    public $id;
    public $label;
    public $checked;
    public $class;
    public $gap;
    public $classInput;
    public $classLabel;

    /**
     * Create a new component instance.
     */
    public function __construct($id, $label, $checked = false, $class = "secondary", $gap = 20, $classInput = "", $classLabel = "")
    {
        $this->id = $id;
        $this->label = $label;
        $this->checked = $checked;
        $this->class = $class;
        $this->gap = $gap;
        $this->classInput = $classInput;
        $this->classLabel = $classLabel;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.checkbox');
    }
}
