<?php

namespace Tenancy\Providers;

use Tenancy\ActiveTenant;
use Tenancy\Extensions\TenantGuard;
use Tenancy\Extensions\TenantUserProvider;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Tenancy\Contracts\TenantContract;
use Event;

class TenantServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(UrlGenerator $url)
    {
        Auth::provider('tenant', function ($app, array $config) {
            return new TenantUserProvider($app['hash'], $config['model']);
        });

        Event::listen(['eloquent.creating: *'], function($event, $models) {
            if (!tenant()->get()) {
                return;
            }

            foreach ($models as $model) {
                if (in_array(TenantContract::class,  class_implements($model))) {
                    if (!$model->is_scoped && !is_null($model->is_scoped)) {
                        if (!$model->tenantThroughRelation()) {
                            $model->{tenant()->getColumn()} = tenant()->getId();
                        }
                    }
                }
            }
        });

        Event::listen('eloquent.updating: *', function($event, &$models) {
            if (!tenant()->get()) {
                return;
            }

            foreach ($models as &$model) {
                if (in_array(TenantContract::class,  class_implements($model))) {
                    if (!$model->is_scoped && !is_null($model->is_scoped)) {
                        if (!$model->tenantThroughRelation()) {
                            $model->{tenant()->getColumn()} = tenant()->getId();

                            $clone = $model->replicate();
                            $clone->save();

                            if (method_exists($model, 'replicateWithRelations')) {
                                $model->load(...$model->replicateWithRelations());

                                //re-sync everything
                                foreach ($model->getRelations() as $relationName => $items) {
                                    foreach ($items as $item) {
                                        $itemClone = $item->replicate();
                                        if (method_exists($itemClone, 'toFlatArray')) {
                                            $duplicatedData = $itemClone->toFlatArray();
                                        } else {
                                            $duplicatedData = $itemClone->toArray();
                                        }

                                        $clone->$relationName()->create($duplicatedData);
                                    }
                                }
                            }

                            $model->fill($clone->toArray());

                            return false;
                        }
                    }
                }
            }
        });
    }
    /**
     * Define the resources used by Rocket.
     *
     * @return void
     */
    public function register()
    {
        $tenant = new ActiveTenant();
        App::bind(ActiveTenant::class, function() use ($tenant) {
            return $tenant;
        });

        $helpers = __DIR__.'/../helpers.php';

        if (file_exists($helpers)) {
            require_once($helpers);
        }
    }
}
