<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function show($identifier)
    {
        // جلب الباقة إما عن طريق الـ slug أو عن طريق الـ id
        $plan = Plan::where('slug', $identifier)->orWhere('id', $identifier)->firstOrFail();

        return view('plans.show', compact('plan'));
    }
}