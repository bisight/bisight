<?php

namespace BiSight\Portal\Model;

use Radvance\Model\BaseModel;
use Radvance\Model\Space;
use Radvance\Model\SpaceInterface;

class Warehouse extends Space implements SpaceInterface
{
    protected $id;
    protected $name;
    protected $account_name;
    protected $description;
    protected $connection;
}
