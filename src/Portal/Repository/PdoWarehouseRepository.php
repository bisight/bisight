<?php
namespace BiSight\Portal\Repository;

use Radvance\Repository\BaseRepository;
use Radvance\Repository\PdoSpaceRepository;
use Radvance\Repository\SpaceRepositoryInterface;
use BiSight\Portal\Model\Warehouse;
use PDO;

class PdoWarehouseRepository extends PdoSpaceRepository implements SpaceRepositoryInterface
{
    // the model class of space
    protected $modelClassName = '\BiSight\Portal\Model\Warehouse';
    // the name of the space, to be used in UI
    protected $nameOfSpace = 'Warehouse';
    // the plural name of the space, to be used in UI. Optional
    protected $nameOfSpacePlural = 'Warehouses';
    // the permission table name.
    protected $permissionTableName = 'permission';
    // the foreign key name in the permission table that links to space
    protected $permissionTableForeignKeyName = 'warehouse_id';
    
    public function createEntity()
    {
        return Warehouse::createNew();
    }
    
    public function findOneByAccountNameAndName($accountName, $name)
    {
        return $this->findOneBy(array('account_name' => $accountName, 'name' => $name));
    }
}
