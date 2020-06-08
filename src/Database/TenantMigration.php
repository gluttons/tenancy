<?php

namespace App\Modules\Tenancy\Database;

class TenantMigration
{
    public static function defaults(&$table)
    {
        $table->bigIncrements('id');
        $table->string('code')->unique();
        $table->string('domain')->unique();
        $table->boolean('active')->default(false);
    }
}
