<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => [
                'required',
                function (string $attribute, mixed $value, \Closure $fail) use ($user) {
                    $val = (string) $value;
                    $doc = trim((string) ($user->documento_identidad ?? ''));
                    $user->loadMissing('cliente');
                    $docCliente = trim((string) ($user->cliente?->documento ?? ''));

                    $matchHash = Hash::check($val, $user->getAuthPassword());
                    $matchDoc = ($doc !== '' && hash_equals($doc, $val))
                        || ($docCliente !== '' && hash_equals($docCliente, $val));

                    if (! $matchHash && ! $matchDoc) {
                        $fail(__('The provided password does not match your current password.'));
                    }
                },
            ],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ]);

        $user->assignPassword($validated['password']);
        $user->save();

        return back()->with('status', 'password-updated');
    }
}
