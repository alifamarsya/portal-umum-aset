<?php

namespace App\Providers;

use App\Models\AsMemoSewaCabang;
use App\Models\AsPks;
use App\Models\PgSpk;
use App\Models\UmBiayaHarian;
use App\Models\UmPermintaanCabang;
use App\Policies\ApprovalPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Semua model dengan alur Maker-Checker memakai satu ApprovalPolicy
        // yang sama (CPMK Blockchain: checker != maker).
        foreach ([UmBiayaHarian::class, UmPermintaanCabang::class, AsPks::class, AsMemoSewaCabang::class, PgSpk::class] as $model) {
            Gate::policy($model, ApprovalPolicy::class);
        }

        View::composer('*', function ($view) {
            $user = auth()->user();
            $view->with([
                'user'      => $user,
                'canAccess' => fn (string $key) => $user ? $user->canAccessModule($key) : false,
                'canWrite'  => fn (string $key) => $user ? $user->canWriteModule($key) : false,
                'isChecker' => fn () => $user ? $user->isChecker() : false,
            ]);
        });
    }
}
