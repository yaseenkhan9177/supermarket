<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\CompanySetting;

class GeneralSettingsController extends Controller
{
    public function index()
    {
        $settings = CompanySetting::firstOrNew(['id' => 1]);
        return view('settings.general', compact('settings'));
    }

    public function access()
    {
        return view('settings.access');
    }

    public function users()
    {
        return view('settings.users');
    }

    public function todo()
    {
        return view('settings.todo');
    }

    public function reminder()
    {
        return view('settings.reminder');
    }

    public function employees()
    {
        return view('settings.employees');
    }
}
