<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(): View
    {
        $plans = Plan::with('features')->where('is_active', true)->orderBy('sort_order')->get();

        return view('public.home', compact('plans'));
    }

    public function features(): View
    {
        return view('public.features');
    }

    public function pricing(): View
    {
        $plans = Plan::with('features')->where('is_active', true)->orderBy('sort_order')->get();

        return view('public.pricing', compact('plans'));
    }

    public function about(): View
    {
        return view('public.about');
    }

    public function contact(): View
    {
        return view('public.contact');
    }
}
