<?php

namespace BiSight\Portal\Repository;

use Radvance\Repository\PermissionRepositoryInterface;
use Radvance\Repository\PdoPermissionRepository as BaseRepository;

class PdoPermissionRepository extends BaseRepository implements PermissionRepositoryInterface
{
    // the model class of permission
    protected $modelClassName = '\BiSight\Portal\Model\Permission';
    // the foreign key name in the permission table that links to space
    protected $spaceTableForeignKeyName = 'warehouse_id';
}
