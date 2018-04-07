<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'  => 'required',
            'email' => ['required', Rule::unique('tenant.users')->ignore(Auth::id())],
        ];
    }

    public function commit()
    {
        $this->user()->update(['name' => $this->name, 'email' => $this->email]);
    }
}
