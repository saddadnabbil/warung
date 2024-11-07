<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\User;
use App\Policies\MenuPolicy;
use App\Policies\ActivityPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\ExceptionPolicy;
use App\Models\Blog\Post as BlogPost;
use Spatie\Activitylog\Models\Activity;
use Datlechin\FilamentMenuBuilder\Models\Menu;
use App\Models\Blog\Category as BlogPostCategory;
use App\Models\Customer;
use App\Policies\Blog\PostPolicy as BlogPostPolicy;
use BezhanSalleh\FilamentExceptions\Models\Exception;
use App\Policies\Blog\CategoryPolicy as BlogPostCategoryPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Activity::class => ActivityPolicy::class,
        Menu::class => MenuPolicy::class,
        Customer::class => CustomerPolicy::class,
        Exception::class => ExceptionPolicy::class,
        'Spatie\Permission\Models\Role' => 'App\Policies\RolePolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
