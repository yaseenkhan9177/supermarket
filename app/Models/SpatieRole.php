<?php

namespace App\Models;

use Spatie\Permission\Models\Role as BaseRole;

class SpatieRole extends BaseRole
{
    /**
     * Force the central database connection for all roles.
     * This avoids connection mismatch issues when verifying user roles.
     *
     * @var string
     */
    protected $connection = 'mysql';
}
