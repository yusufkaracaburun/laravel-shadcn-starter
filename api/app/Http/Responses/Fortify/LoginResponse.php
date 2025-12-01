<?php

declare(strict_types=1);

namespace App\Http\Responses\Fortify;

use Laravel\Fortify\Fortify;
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
        if ($request->wantsJson() || $request->expectsJson() || $request->isXmlHttpRequest()) {
            return response()->json([
                'user' => $request->user(),
                'two_factor' => false,
            ]);
        }

        return redirect()->intended(Fortify::redirects('login'));
    }
}

