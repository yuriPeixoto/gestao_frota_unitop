<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class DropdownMenu extends Component
{
    public $buttonText;
    public $buttonIcon;
    public $menuItems;
    public $buttonClass;
    public $menuClass;

    public function __construct(
        $buttonText = 'Ações',
        $buttonIcon = 'gear',
        $menuItems = [],
        $buttonClass = '',
        $menuClass = ''
    ) {
        $this->buttonText = $buttonText;
        $this->buttonIcon = $buttonIcon;
        $this->menuItems = $menuItems;
        $this->buttonClass = $buttonClass;
        $this->menuClass = $menuClass;
    }

    public function render()
    {
        return view('components.dropdown-menu');
    }
}