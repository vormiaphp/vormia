<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Services\Vrm\TokenService;
use App\Jobs\V1\SendPasswordResetJob;
use App\Jobs\V1\SendPasswordUpdatedJob;
use App\Traits\Vrm\Model\ApiResponseTrait;
use Illuminate\Support\Str;

class AuthResetPasswordController extends Controller
{
    use ApiResponseTrait;

    /**
     * Handle password reset request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function passwordReset(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "email" => ["required", "string", "email", "max:255"],
            ]);

            if ($validator->fails()) {
                return $this->validationError(
                    $validator->errors()->toArray(),
                    "Validation errors",
                );
            }

            $user = User::where("email", $request->email)->first();

            if (!$user) {
                return $this->error(
                    "If your email exists in our system, you will receive a password reset link.",
                    200,
                );
            }

            // Generate password reset token
            $tokenService = app(TokenService::class);
            $resetToken = $tokenService->generateToken(
                $user->id,
                "password_reset",
                "password_reset",
                null,
                60, // 1 hour expiry
            );

            // Create reset URL
            $resetUrl = url(
                "/api/v1/password-reset/verify?token=" . $resetToken,
            );

            // Dispatch job to send password reset email
            dispatch(new SendPasswordResetJob($user, $resetUrl));

            return $this->success(
                [
                    "reset_url" => $resetUrl,
                    "reset_token" => $resetToken,
                ],
                "If your email exists in our system, you will receive a password reset link.",
                201,
            );
        } catch (\Exception $e) {
            return $this->error(
                "Failed to process password reset: " . $e->getMessage(),
                500,
            );
        }
    }

    /**
     * Handle password reset verification and update.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function passwordResetVerify(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                "token" => ["required", "string"],
                "password" => [
                    "required",
                    "confirmed",
                    "string",
                    "min:4",
                    "regex:/[a-z]/", // must contain at least one lowercase letter
                    "regex:/[A-Z]/", // must contain at least one uppercase letter
                    "regex:/[0-9]/", // must contain at least one digit
                ],
            ]);

            if ($validator->fails()) {
                return $this->validationError(
                    $validator->errors()->toArray(),
                    "Validation errors",
                );
            }

            $tokenService = app(TokenService::class);
            $authToken = $tokenService->verifyToken(
                $request->token,
                "password_reset",
                "password_reset",
                null,
                true,
            );

            if (!$authToken) {
                return $this->error(
                    "Invalid or expired password reset token.",
                    400,
                );
            }

            $user = User::find($authToken->user_id);
            if (!$user) {
                return $this->error("User not found.", 404);
            }

            // Update password
            $user->password = Hash::make($request->password);
            $user->save();

            // Revoke all tokens (optional, for security)
            $user->tokens()->delete();

            // Delete the used token
            $authToken->delete();

            // Dispatch job to send password updated notification
            dispatch(new SendPasswordUpdatedJob($user));

            return $this->success(
                [
                    "user" => $user,
                ],
                "Password has been reset successfully.",
                201,
            );
        } catch (\Exception $e) {
            return $this->error(
                "Failed to reset password: " . $e->getMessage(),
                500,
            );
        }
    }
}
