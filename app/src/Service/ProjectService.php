<?php

namespace Service;

use Doctrine\ORM\EntityManager;
use Entity\Project;

class ProjectService
{

    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return \Entity\ProjectRepository
     */
    private function getRepository()
    {
        return $this->entityManager->getRepository(Project::class);
    }

    /**
     * @param int $id
     * @return \Entity\Project|null
     */
    public function findById($id)
    {
        return $this->entityManager->find(Project::class, $id);
    }

    /**
     * @return \Entity\Project[]
     */
    public function getProjects()
    {
        return $this->getRepository()
            ->getItemsQuery()
            ->getQuery()
            ->getResult();
    }

    public function save(Project $project)
    {
        if ($project->getId() === null) {
            $project->setEnabled(true);
            $this->entityManager->persist($project);
        }

        $this->entityManager->flush($project);
    }

}