<?php

namespace App;

use Hyn\Tenancy\Traits\UsesTenantConnection;
use Spatie\Permission\Models\Role as BaseRole;

class Role extends BaseRole
{
    use UsesTenantConnection;
}
