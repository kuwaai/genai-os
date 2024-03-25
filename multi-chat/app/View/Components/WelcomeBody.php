<?php

namespace App\View\Components;

use Illuminate\View\Component;

class WelcomeBody extends Component
{
    public function render()
    {
        if (view()->exists('components.custom.welcome_body')) {
            return view('components.custom.welcome_body');
        }

        return view('components.welcome_body');
    }
}
