<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log; // Pastikan ini diimpor

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $login = $this->input('login');
        $password = $this->input('password');
        $remember = $this->boolean('remember');

        // 1. Coba login menggunakan NIDN
        if (Auth::attempt(['nidn' => $login, 'password' => $password], $remember)) {
            RateLimiter::clear($this->throttleKey());
            return; // Berhasil, berhenti di sini
        }

        // 2. Jika gagal, coba login menggunakan NIM
        if (Auth::attempt(['nim' => $login, 'password' => $password], $remember)) {
            RateLimiter::clear($this->throttleKey());
            return; // Berhasil, berhenti di sini
        }

        // 3. Jika masih gagal, coba sebagai email
        if (Auth::attempt(['email' => $login, 'password' => $password], $remember)) {
            RateLimiter::clear($this->throttleKey());
            return; // Berhasil, berhenti di sini
        }

        // 4. Jika semua gagal, lemparkan error
        RateLimiter::hit($this->throttleKey());
        throw ValidationException::withMessages([
            'login' => trans('auth.failed'),
        ]);
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('login')) . '|' . $this->ip());
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->has('login')) {
                $validator->errors()->add('login', trans('auth.failed'));
            }
        });
    }
}
