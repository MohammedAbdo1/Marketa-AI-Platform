<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\UserEmailVerification;
use App\Notifications\EmailChangeVerificationNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class EmailChangeVerificationService
{
    protected const CODE_EXPIRATION_MINUTES = 10;
    protected const RESEND_COOLDOWN_SECONDS = 60;
    protected const MAX_ATTEMPTS = 5;

    public function requestCode(User $user): void
    {
        $recentRequest = UserEmailVerification::where('user_id', $user->id)
            ->whereNull('consumed_at')
            ->latest()
            ->first();

        if ($recentRequest && $recentRequest->created_at->diffInSeconds(now()) < self::RESEND_COOLDOWN_SECONDS) {
            $retryAfter = self::RESEND_COOLDOWN_SECONDS - $recentRequest->created_at->diffInSeconds(now());
            throw new TooManyRequestsHttpException($retryAfter, __('Please wait before requesting another code.'));
        }

        // Clean up expired records
        UserEmailVerification::where('user_id', $user->id)
            ->whereNull('consumed_at')
            ->where('expires_at', '<', now())
            ->delete();

        $code = (string) random_int(100000, 999999);

        UserEmailVerification::create([
            'user_id' => $user->id,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::CODE_EXPIRATION_MINUTES),
        ]);

        $user->notify(new EmailChangeVerificationNotification($code));
    }

    public function verifyCode(User $user, string $code): string
    {
        $verification = UserEmailVerification::where('user_id', $user->id)
            ->whereNull('consumed_at')
            ->whereNull('verification_token')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (! $verification) {
            throw ValidationException::withMessages([
                'code' => [__('The verification code is invalid or has expired.')],
            ]);
        }

        if ($verification->attempts >= self::MAX_ATTEMPTS) {
            throw ValidationException::withMessages([
                'code' => [__('Too many attempts. Please request a new code.')],
            ]);
        }

        if (! Hash::check($code, $verification->code_hash)) {
            $verification->increment('attempts');

            throw ValidationException::withMessages([
                'code' => [__('The verification code is incorrect.')],
            ]);
        }

        $verification->forceFill([
            'verified_at' => now(),
            'verification_token' => Str::uuid(),
            'attempts' => 0,
        ])->save();

        return $verification->verification_token;
    }

    public function updateEmail(User $user, string $token, string $newEmail): User
    {
        $verification = UserEmailVerification::where('user_id', $user->id)
            ->where('verification_token', $token)
            ->whereNull('consumed_at')
            ->whereNotNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $verification) {
            throw ValidationException::withMessages([
                'verification_token' => [__('The verification token is invalid or has expired.')],
            ]);
        }

        return DB::transaction(function () use ($user, $verification, $newEmail) {
            $user->email = $newEmail;
            $user->email_verified_at = null;
            $user->save();

            $verification->forceFill([
                'consumed_at' => now(),
            ])->save();

            if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail) {
                $user->sendEmailVerificationNotification();
            }

            return $user;
        });
    }
}

