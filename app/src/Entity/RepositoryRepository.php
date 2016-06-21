<?php

namespace Entity;

use Doctrine\ORM\EntityRepository;

/**
 * RepositoryRepository
 */
class RepositoryRepository extends EntityRepository
{

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getItemsQuery()
    {
        return $this->createQueryBuilder("r")
            ->where("r.enabled = :enabled")->setParameter("enabled", 1)
            ->orderBy("r.title", "ASC");
    }

}
