<?php

namespace BiSight\Portal\Model;

use Radvance\Model\Permission as BasePermission;
use Radvance\Model\PermissionInterface;

class Permission extends BasePermission implements PermissionInterface
{
    // only need to put the permission-to-space foreignkey property here, nothing else
    protected $warehouse_id;
}
