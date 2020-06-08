<?php

namespace Tenancy\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\UnauthorizedException;
use Tenancy\Scopes\TenantScope;

trait Tenant {

    /**
     * Boot the scope.
     *
     * @return void
     */
    public static function bootTenant()
    {
        static::addGlobalScope(new TenantScope);
    }

    /**
     * Get the name of the column for applying the scope.
     *
     * @return string
     */
    public function getIdForTenant()
    {
        return tenant()->getId();
    }

    /**
     * Get the name of the column for applying the scope.
     *
     * @return string
     */
    public function getColumnForTenant()
    {
        return tenant()->getColumn();
    }

    /**
     * @return string
     */
    public function getGroupColumnForTenant()
    {
        return 'id';
    }

    /**
     * @return mixed
     */
    public function getTableForTenant()
    {
        $relation = $this->tenantThroughRelation();
        if ($relation) {
            return $relation['table'];
        }

        return $this->getTable();
    }

    /**
     * @return string
     */
    public function getQualifiedColumnForTenant()
    {
        return $this->getTableForTenant() . '.' . $this->getColumnForTenant();
    }

    /**
     * @return string
     */
    public function getQualifiedGroupColumnForTenant()
    {
        return $this->getTableForTenant() . '.' . $this->getGroupColumnForTenant();
    }

    /**
     * Get table relation if scope is derived
     *
     * @return bool|array
     */
    public function tenantThroughRelation()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function getIsScopedAttribute()
    {
        if ($this->tenantThroughRelation()) {
            return !is_null($this->{$this->tenantThroughRelation()['attribute']}->{$this->getColumnForTenant()});
        }

        return !is_null($this->{$this->getColumnForTenant()});
    }

    /**
     * Get the query builder without the scope applied.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function withoutTenantScope()
    {
        return with(new static)->newQueryWithoutScope(new TenantScope);
    }

    /**
     * Perform the actual delete query on this model instance.
     *
     * @return void
     */
    protected function performDeleteOnModel()
    {
        if (!$this->getIsScopedAttribute()) {
            throw new UnauthorizedException();
        }

        $relations = $this->tenantThroughRelation();
        if (!$relations) {
            $this->tenantKeysForSaveQuery(
                $this->newQueryWithoutScopes())->where($this->getColumnForTenant(),
                $this->{$this->getColumnForTenant()}
            )->delete();

            $this->exists = false;
            return;
        }

        if ($this->{$relations['attribute']}->{$this->getColumnForTenant()} == $this->getIdForTenant()) {
            return parent::performDeleteOnModel();
        }

        throw new UnauthorizedException();
    }

    protected function tenantKeysForSaveQuery(Builder $query)
    {
        $query = parent::tenantKeysForSaveQuery($query);

        if ($this->getIsScopedAttribute()) {
            $relations = $this->tenantThroughRelation();
            if (!$relations) {
                $query->where(
                    $this->getQualifiedColumnForTenant(),
                    '=',
                    $this->getAttribute($this->getColumnForTenant())
                );
            }
        }

        return $query;
    }
}
