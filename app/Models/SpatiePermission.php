<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as BasePermission;

class SpatiePermission extends BasePermission
{
    /**
     * Force the central database connection for all permissions.
     * This avoids connection mismatch issues when verifying user permissions.
     *
     * @var string
     */
    protected $connection = 'mysql';
}
