<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Pim\Bundle\CatalogBundle\Doctrine\EntityRepository;

/**
 * Repository
 *
 * @author    Nicolas <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeRepository extends EntityRepository
{
    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function buildAll()
    {
        return $this->build()
            ->addOrderBy('group_type.code', 'ASC');
    }
}
