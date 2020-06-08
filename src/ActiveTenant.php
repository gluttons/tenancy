<?php

namespace App\Modules\Tenancy;

use App\Modules\Tenancy\Exceptions\TenantNotFoundException;
use App\Modules\Tenancy\Exceptions\TenantNotActiveException;

class ActiveTenant
{
    protected $activeTenant = null;

    public function get()
    {
        return $this->activeTenant;
    }

    public function check() {
        return $this->get() && $this->get()->code;
    }

    public function getColumn()
    {
        return config('tenancy.foreign_key');
    }

    public function getClass()
    {
        return config('tenancy.model');
    }

    public function getId()
    {
        return $this->activeTenant ? $this->activeTenant->id : null;
    }

    public function switchTo($tenant)
    {
        if (!$tenant) {
            throw new TenantNotFoundException();
        }

        if (!$tenant->active) {
            throw new TenantNotActiveException();
        }

        return $this->activeTenant = $tenant;
    }
}
