<?php

namespace App\Http\Middleware;

use App\Models\User;
use Database\Seeders\DefaultUserSeeder;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class AutoLoginForDevelopment
{
    /**
     * Automatically authenticate the first available user in local development.
     */
    public function handle(Request $request, Closure $next)
    {
        if (! App::environment(['local', 'development']) || Auth::check()) {
            return $next($request);
        }

        if (! User::query()->exists()) {
            Artisan::call('db:seed', ['--class' => DefaultUserSeeder::class]);
        }

        $email = trim((string) env('AUTO_LOGIN_EMAIL'));

        $user = $email !== ''
            ? User::query()->where('email', $email)->first()
            : User::query()->first();

        if ($user) {
            Auth::login($user);
            $request->session()->regenerate();
        }

        return $next($request);
    }
}
