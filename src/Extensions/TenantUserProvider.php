<?php

namespace App\Modules\Tenancy\Extensions;

use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Support\Str;

class TenantUserProvider extends EloquentUserProvider
{
    /**
     * Get a new query builder for the model instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model|null  $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function newModelQuery($model = null)
    {
        $model = is_null($model)
            ? $this->createModel()
            : $model;

        $query = $model->newQuery();

        if (tenant()->check() && config('tenancy.pivot_table')) {
            $query->leftJoin(config('tenancy.pivot_table'), Str::singular($model->getTable()) .'_' . $model->getKeyName(), '=', $model->getTable() .'.' . $model->getKeyName());
            $query->where(config('tenancy.foreign_key'), tenant()->get()->id);
        }

        return $query;
    }
}
