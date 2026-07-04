<?php
namespace App\View\Components\Widgets;

use Illuminate\View\Component;

class ApexDonutChart extends Component
{
    public $title;
    public $data;
    public $labels;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($title, $data, $labels)
    {
        $this->title = $title;
        $this->data = $data;
        $this->labels = $labels;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.widgets.apex-donut-chart');
    }
}
