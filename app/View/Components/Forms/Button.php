<?php

namespace App\View\Components\Forms;

use Illuminate\View\Component;

class Button extends Component
{
    /**
     * Button type (primary, secondary, danger, etc.)
     */
    public $type;

    /**
     * Button variant (filled, outlined, text)
     */
    public $variant;

    /**
     * Button size (sm, md, lg)
     */
    public $size;

    /**
     * Button href if using as link
     */
    public $href;

    /**
     * Button form type (button, submit, reset)
     */
    public $buttonType;

    /**
     * Indicates if the button is disabled
     */
    public $disabled;

    /**
     * Additional classes to apply
     */
    public $class;

    /**
     * Create a new component instance.
     *
     * @param  string  $type
     * @param  string  $variant
     * @param  string  $size
     * @param  string|null  $href
     * @param  string  $buttonType
     * @param  bool  $disabled
     * @param  string  $class
     * @return void
     */
    public function __construct(
        $type = 'primary',
        $variant = 'filled',
        $size = 'md',
        $href = null,
        $buttonType = 'button',
        $disabled = false,
        $class = ''
    ) {
        $this->type = $type;
        $this->variant = $variant;
        $this->size = $size;
        $this->href = $href;
        $this->buttonType = $buttonType;
        $this->disabled = $disabled;
        $this->class = $class;
    }

    /**
     * Get the base classes for the button.
     *
     * @return string
     */
    protected function getBaseClasses()
    {
        return 'inline-flex items-center justify-center rounded font-medium transition-all focus:outline-none focus:ring-2 focus:ring-offset-2';
    }

    /**
     * Get the size classes based on the size prop.
     *
     * @return string
     */
    protected function getSizeClasses()
    {
        return [
            'sm' => 'px-2.5 py-1.5 text-xs',
            'md' => 'px-4 py-2 text-sm',
            'lg' => 'px-6 py-3 text-base',
        ][$this->size] ?? 'px-4 py-2 text-sm';
    }

    /**
     * Get the color classes based on the type and variant props.
     *
     * @return string
     */
    protected function getColorClasses()
    {
        $colors = [
            'primary' => [
                'filled' => 'bg-blue-600 text-white hover:bg-blue-700 active:bg-blue-800 focus:ring-blue-500 active:transform active:scale-95',
                'outlined' => 'border border-blue-500 text-blue-600 hover:bg-blue-50 active:bg-blue-100 focus:ring-blue-500 active:transform active:scale-95',
                'text' => 'text-blue-600 hover:bg-blue-50 active:bg-blue-100 focus:ring-blue-500 active:transform active:scale-95',
            ],
            'secondary' => [
                'filled' => 'bg-gray-600 text-white hover:bg-gray-700 active:bg-gray-800 focus:ring-gray-500 active:transform active:scale-95',
                'outlined' => 'border border-gray-300 text-gray-700 hover:bg-gray-50 active:bg-gray-100 focus:ring-gray-500 active:transform active:scale-95',
                'text' => 'text-gray-700 hover:bg-gray-50 active:bg-gray-100 focus:ring-gray-500 active:transform active:scale-95',
            ],
            'success' => [
                'filled' => 'bg-green-600 text-white hover:bg-green-700 active:bg-green-800 focus:ring-green-500 active:transform active:scale-95',
                'outlined' => 'border border-green-500 text-green-600 hover:bg-green-50 active:bg-green-100 focus:ring-green-500 active:transform active:scale-95',
                'text' => 'text-green-600 hover:bg-green-50 active:bg-green-100 focus:ring-green-500 active:transform active:scale-95',
            ],
            'danger' => [
                'filled' => 'bg-red-600 text-white hover:bg-red-700 active:bg-red-800 focus:ring-red-500 active:transform active:scale-95',
                'outlined' => 'border border-red-500 text-red-600 hover:bg-red-50 active:bg-red-100 focus:ring-red-500 active:transform active:scale-95',
                'text' => 'text-red-600 hover:bg-red-50 active:bg-red-100 focus:ring-red-500 active:transform active:scale-95',
            ],
        ];

        return $colors[$this->type][$this->variant] ?? $colors['primary']['filled'];
    }

    /**
     * Get the disabled classes.
     *
     * @return string
     */
    protected function getDisabledClasses()
    {
        if (!$this->disabled) {
            return '';
        }

        switch ($this->variant) {
            case 'filled':
                return 'opacity-50 cursor-not-allowed';
            case 'outlined':
            case 'text':
                return 'opacity-50 cursor-not-allowed';
            default:
                return 'opacity-50 cursor-not-allowed';
        }
    }

    /**
     * Get all classes for the button.
     * 
     * @return string
     */
    public function classes()
    {
        return trim(
            $this->getBaseClasses() . ' ' .
                $this->getSizeClasses() . ' ' .
                $this->getColorClasses() . ' ' .
                $this->getDisabledClasses() . ' ' .
                $this->class
        );
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.forms.button');
    }
}
