<?php

namespace App\View\Components;

use Illuminate\View\Component;

class APPLogo extends Component
{
    public function render()
    {
        if (view()->exists('components.custom.application-logo')) {
            return view('components.custom.application-logo');
        }

        return view('components.application-logo');
    }
}
