<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Hash;
use App\Hashing\PlainHasher;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ganti semua algoritma hashing bawaan menjadi PlainHasher (tanpa enkripsi)
        Hash::extend('bcrypt', function () {
            return new PlainHasher();
        });
        Hash::extend('argon', function () {
            return new PlainHasher();
        });
        Hash::extend('argon2id', function () {
            return new PlainHasher();
        });
    }
}
