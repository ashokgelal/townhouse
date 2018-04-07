<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('tenant.account.profile');
    }

    public function update(UpdateProfileRequest $request)
    {
        $request->commit();
        session()->flash('alert', ['type' => 'success', 'message' => 'Your profile has been updated.']);

        return redirect(route('tenant.account.profile.edit'));
    }
}
