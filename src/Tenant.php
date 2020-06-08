<?php

namespace App\Modules\Tenancy;

use Illuminate\Database\Eloquent\Model;
use App\Modules\Tenancy\Contracts\TenantContract;
use App\Modules\Tenancy\Traits\Tenant as TenantTrait;

abstract class Tenant extends Model implements TenantContract
{
    use TenantTrait;
}
