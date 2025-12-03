<?php

declare(strict_types=1);

namespace App\Http\Responses\Fortify;

use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

final class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     * @return Response
     */
    public function toResponse($request)
    {
        if ($request->wantsJson() || $request->expectsJson() || $request->isXmlHttpRequest()) {
            return response()->json($request->user());
        }

        return redirect()->intended(Fortify::redirects('login'));
    }
}
