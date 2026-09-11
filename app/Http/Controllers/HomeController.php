<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        return view('pages.home');
    }

    public function rules()
    {
        return view('pages.rules');
    }

    public function staff()
    {
        return view('pages.staff');
    }
}
