<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Logo extends Component
{
    public function render()
    {
        if (view()->exists('components.custom.logo')) {
            return view('components.custom.logo');
        }

        return view('components.logo');
    }
}
