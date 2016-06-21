<?php

namespace Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ServerFtpRepository
 */
class ServerFtpRepository extends EntityRepository
{

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getItemsQuery()
    {
        return $this->createQueryBuilder("e")
            ->where("e.enabled = :enabled")->setParameter("enabled", 1)
            ->orderBy("e.title", "ASC");
    }

}
