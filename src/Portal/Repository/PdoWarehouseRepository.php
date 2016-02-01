<?php
namespace BiSight\Portal\Repository;

use Radvance\Repository\BaseRepository;
use Radvance\Repository\RepositoryInterface;
use BiSight\Portal\Model\Warehouse;
use PDO;

class PdoWarehouseRepository extends BaseRepository implements RepositoryInterface
{
    public function createEntity()
    {
        return Warehouse::createNew();
    }
    
    public function findOneByAccountNameAndName($accountName, $name)
    {
        return $this->findOneBy(array('account_name' => $accountName, 'name' => $name));
    }
}
