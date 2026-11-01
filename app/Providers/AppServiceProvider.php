<?php

namespace App\Providers;

use App\Modules\AutoRo\Domain\Interfaces\AutoRoGetInterface;
use App\Modules\AutoRo\Domain\Interfaces\AutoRoPostInterface;
use App\Modules\AutoRo\Infrastructure\Repositories\AutoRoRepository;
use App\Modules\Bonus\Domain\Interfaces\BonusGetInterface;
use App\Modules\Bonus\Domain\Interfaces\BonusPostInterface;
use App\Modules\Bonus\Infrastructure\Repositories\BonusRepository;
use App\Modules\EWallet\Domain\Interfaces\EWalletGetInterface;
use App\Modules\EWallet\Domain\Interfaces\EWalletPostInterface;
use App\Modules\EWallet\Infrastructure\Repositories\EWalletRepository;
use App\Modules\Member\Domain\Interfaces\MemberGetInterface;
use App\Modules\Member\Domain\Interfaces\MemberPostInterface;
use App\Modules\Member\Infrastructure\Repositories\MemberRepository;
use App\Modules\Product\Domain\Interfaces\ProductGetInterface;
use App\Modules\Product\Domain\Interfaces\ProductPostInterface;
use App\Modules\Product\Infrastructure\Repositories\ProductRepository;
use App\Modules\Withdraw\Domain\Interfaces\WithdrawGetInterface;
use App\Modules\Withdraw\Domain\Interfaces\WithdrawPostInterface;
use App\Modules\Withdraw\Infrastructure\Repositories\WithdrawRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            ProductGetInterface::class,
            ProductRepository::class
        );
        $this->app->bind(
            ProductPostInterface::class,
            ProductRepository::class
        );
        $this->app->bind(
            WithdrawGetInterface::class,
            WithdrawRepository::class
        );
        $this->app->bind(
            WithdrawPostInterface::class,
            WithdrawRepository::class
        );
        $this->app->bind(
            AutoRoGetInterface::class,
            AutoRoRepository::class
        );
        $this->app->bind(
            AutoRoPostInterface::class,
            AutoRoRepository::class
        );
        $this->app->bind(
            EWalletGetInterface::class,
            EWalletRepository::class
        );
        $this->app->bind(
            EWalletPostInterface::class,
            EWalletRepository::class
        );
        $this->app->bind(
            BonusGetInterface::class,
            BonusRepository::class
        );
        $this->app->bind(
            BonusPostInterface::class,
            BonusRepository::class
        );
        $this->app->bind(
            MemberGetInterface::class,
            MemberRepository::class
        );
        $this->app->bind(
            MemberPostInterface::class,
            MemberRepository::class
        );
        $this->app->bind(
            MemberGetInterface::class,
            MemberRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(
            fn (): ?Password => app()->isProduction()
                ? Password::min(12)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
                : null,
        );
    }
}
