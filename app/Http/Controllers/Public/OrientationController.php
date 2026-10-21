<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Laravel\Head\Facades\Head;

class OrientationController extends Controller
{
    /**
     * Page d'orientation et de sélection du profil d'accès.
     */
    public function index(): View
    {
        Head::title('Rejoindre Hubs Africa — Orientation & Choix de Profil')
            ->description('Sélectionnez votre profil (Entrepreneur, Mentor expert, Structure SAE) pour accéder aux programmes ou à votre espace dédié.');

        return view('pages.rejoindre');
    }
}
