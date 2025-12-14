<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\Support\Responsable;

final class OAuthAccountLinkingException extends Exception implements Responsable
{
    public const string EXISTING_CONNECTION_ERROR_MESSAGE = 'Please login with your existing authentication method.';

    public static function emailMismatch(string $provider): self
    {
        return new self(__('The email address from this :provider does not match your account email.', ['provider' => $provider]));
    }

    public static function existingConnection(): self
    {
        return new self(self::EXISTING_CONNECTION_ERROR_MESSAGE);
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     * @return Response|JsonResponse|RedirectResponse
     */
    public function toResponse($request)
    {
        if ($request->expectsJson()) {
            return ApiResponse::validationError($this->getMessage());
        }

        return back()->with('error', $this->getMessage());
    }
}
