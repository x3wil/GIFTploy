<?php

namespace Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ServerRepository
 */
class ServerRepository extends EntityRepository
{

    /**
     * @param int $environmentId
     * @return \Entity\Server|null
     */
    public function getDefault($environmentId)
    {
        $query = $this->createQueryBuilder('c')
            ->andWhere('c.default = :default')->setParameter('default', true)
            ->andWhere('c.environment = :environment')->setParameter('environment', $environmentId)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

}
