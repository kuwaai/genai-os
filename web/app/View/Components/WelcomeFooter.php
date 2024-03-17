<?php

namespace App\View\Components;

use Illuminate\View\Component;

class WelcomeFooter extends Component
{
    public function render()
    {
        if (view()->exists('components.custom.welcome_footer')) {
            return view('components.custom.welcome_footer');
        }

        return view('components.welcome_footer');
    }
}
