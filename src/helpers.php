<?php

if (! function_exists('tenant')) {

    function tenant()
    {
        return resolve(\Tenancy\ActiveTenant::class);
    }

}
