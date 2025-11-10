<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Tooltip extends Component
{
    /**
     * O conteúdo do tooltip.
     *
     * @var string
     */
    public $content;

    /**
     * A posição do tooltip.
     *
     * @var string
     */
    public $placement;

    /**
     * O tema do tooltip.
     *
     * @var string
     */
    public $theme;

    /**
     * A animação do tooltip.
     *
     * @var string
     */
    public $animation;

    /**
     * Se o tooltip deve ter seta.
     *
     * @var bool
     */
    public $arrow;

    /**
     * O atraso na exibição do tooltip.
     *
     * @var int|array
     */
    public $delay;

    /**
     * A duração da animação do tooltip.
     *
     * @var int|array
     */
    public $duration;

    /**
     * O gatilho para exibir o tooltip.
     *
     * @var string
     */
    public $trigger;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $content = '',
        string $placement = 'top',
        string $theme = 'light',
        string $animation = 'scale',
        bool $arrow = true,
        $delay = 0,
        $duration = [300, 250],
        string $trigger = 'mouseenter focus'
    ) {
        $this->content = $content;
        $this->placement = $placement;
        $this->theme = $theme;
        $this->animation = $animation;
        $this->arrow = $arrow;
        $this->delay = $delay;
        $this->duration = $duration;
        $this->trigger = $trigger;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.tooltip');
    }
}