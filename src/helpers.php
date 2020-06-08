<?php

if (! function_exists('tenant')) {

    function tenant()
    {
        return resolve(\App\Modules\Tenancy\ActiveTenant::class);
    }

}
