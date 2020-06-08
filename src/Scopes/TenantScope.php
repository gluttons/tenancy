<?php

namespace App\Modules\Tenancy\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\DB;

class TenantScope implements Scope
{

    public function apply(Builder $builder, Model $model)
    {
        $builder->whereIn($model->getTable() . '.id', function($query) use ($model)
        {
            $query->from($model->getTable())
                ->select($model->getTable() . '.id');

            if ($model->tenantThroughRelation()) {
                $relation = $model->tenantThroughRelation();
                $foreignColumn = isTenant($relation['foreign_column']) ? $relation['foreign_column'] : 'id';

                $query->join($relation['table'], function ($join) use ($relation, $model, $foreignColumn) {
                    $join->on($relation['table'] . '.' . $foreignColumn, '=', $model->getTable() . '.' . $relation['column']);
                });
            }

            $query->whereIn($model->getTableForTenant() . '.id', function ($query) use ($model) {
                $query->from($model->getTableForTenant())
                    ->select($model->getTableForTenant() . '.id')
                    ->where('filter.' . $model->getGroupColumnForTenant())
                    ->whereNested(function ($nested) use ($model) {
                        $nested->where($model->getQualifiedColumnForTenant(), '=', $model->getIdForTenant())
                            ->orWhere($model->getQualifiedColumnForTenant());
                    })
                    ->join($model->getTableForTenant() . ' as filter', function ($join) use ($model) {
                        $join->on(DB::raw("COALESCE({$model->getQualifiedGroupColumnForTenant()},0)"), '=', DB::raw("COALESCE(filter.{$model->getGroupColumnForTenant()},0)"))
                            ->whereNested(function ($nested) use ($model) {
                                $nested->where("filter.{$model->getColumnForTenant()}", '=', $model->getIdForTenant())
                                    ->orWhere("filter.{$model->getColumnForTenant()}");
                            })
                            ->whereNested(function ($join2) use ($model) {
                                $join2->whereColumn(DB::raw("COALESCE({$model->getQualifiedGroupColumnForTenant()},0)"), '<', DB::raw("COALESCE(filter.{$model->getGroupColumnForTenant()},0)"))
                                    ->whereNested(function ($join3) use ($model) {
                                        $join3->on(DB::raw("COALESCE({$model->getQualifiedGroupColumnForTenant()},0)"), '=', DB::raw("COALESCE(filter.{$model->getGroupColumnForTenant()},0)"))
                                            ->whereColumn($model->getTableForTenant() . '.id', '<', 'filter.id', 'AND');
                                    }, 'or');
                            });
                    }, null, null, 'left outer');
            });
        });
    }
}
