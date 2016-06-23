<?php

namespace Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ProjectRepository
 */
class ProjectRepository extends EntityRepository
{

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getItemsQuery()
    {
        return $this->createQueryBuilder("p")
            ->where("p.enabled = :enabled")->setParameter("enabled", 1)
            ->orderBy("p.title", "ASC");
    }

}
