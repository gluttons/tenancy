<?php

namespace Tenancy;

use Illuminate\Database\Eloquent\Model;
use Tenancy\Contracts\TenantContract;
use Tenancy\Traits\Tenant as TenantTrait;

abstract class Tenant extends Model implements TenantContract
{
    use TenantTrait;
}
