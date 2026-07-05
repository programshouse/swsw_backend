<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralPointRule;
use Illuminate\Http\Request;

class ReferralPointRuleController extends Controller
{
     public function index()
    {
        $rules = ReferralPointRule::latest()->get();

        return view('admin.referral-point-rules.index', compact('rules'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'users_count' => 'required|integer|min:1',
            'points' => 'required|integer|min:1',
        ]);

        ReferralPointRule::create($validated);

        return back()->with('success', 'تم إضافة القاعدة بنجاح');
    }

    public function destroy(ReferralPointRule $rule)
    {
        $rule->delete();

        return back()->with('success', 'تم حذف القاعدة بنجاح');
    }
}
