<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

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
        Passport::hashClientSecrets();
        Passport::tokensCan([
            'create-clients' => 'Create Clients',
            'update-clients' => 'Update Clients',
            'view-clients' => 'View Clients',
            'delete-clients' => 'Delete Clients',
            'create-users' => 'Create Users',
            'update-users' => 'Update Users',
            'view-users' => 'View Users',
            'delete-users' => 'Delete Users',
            'view-all-clients' => 'View all Clients',
            'view-all-users' => 'View all Users',
            'default-scope' => 'Default scope for new Users',
            'create-transactions' => 'Create Transactions',
            'update-transactions' => 'Update Transactions',
            'view-transactions' => 'View Transactions',
            'delete-transactions' => 'Delete Transactions',
            'import-transactions' => 'Import Transactions',
            'create-accounts' => 'Create Accounts',
            'update-accounts' => 'Update Accounts',
            'view-accounts' => 'View Accounts',
            'delete-accounts' => 'Delete Accounts',
        ]);
        Passport::loadKeysFrom(__DIR__ . '/../secrets/oauth');
        Passport::enablePasswordGrant();

        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->from(env('MAIL_FROM_ADDRESS'), 'PWF Australia')
                ->subject('Verify Email Address')
                ->line('Click the button below to verify your email address.')
                ->action('Verify Email Address', $url);
        });


        // TO DO: Implement authentication for laravel/pulse and laravel/horizon webui.

        // Gate::define('viewPulse', function (User $user) {
        //     return $user->isAdmin();
        // });

        Gate::define('viewHorizon', function (User $user) {
            return true;
        });
    }
}
