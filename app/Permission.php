<?php

namespace App;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Spatie\Permission\Models\Permission as BasePermission;

class Permission extends BasePermission
{
    use UsesTenantConnection;
}
