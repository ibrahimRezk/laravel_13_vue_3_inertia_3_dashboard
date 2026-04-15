<?php

namespace App\Http\Responses;

use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract 
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        return $request->wantsJson()
            ? response()->json(['two_factor' => false])
            : redirect()->intended();
            // : redirect()->intended($this->redirect());

    }

    // public function redirect()
    // {
    //     if (auth()->user()->hasRole('Super Admin')) {
    //         return  route('dashboard');
    //     } else if (auth()->user()->hasRole('Employee')) {
    //         return  route('dashboard'); // to be changed to employee route after making it
    //     } else {
    //         return '/'; // make general dashboard with last login time 
    //     }
    // }
}
