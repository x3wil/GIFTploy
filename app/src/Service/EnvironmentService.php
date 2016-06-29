<?php

namespace Service;

use Doctrine\ORM\EntityManager;
use Entity\Environment;
use Entity\Project;
use Symfony\Component\Security\Acl\Exception\Exception;

class EnvironmentService
{

    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return \Entity\EnvironmentRepository
     */
    private function getRepository()
    {
        return $this->entityManager->getRepository(Environment::class);
    }

    /**
     * @param int $id
     * @return \Entity\Environment|null
     */
    public function findById($id)
    {
        return $this->entityManager->find(Environment::class, $id);
    }

    /**
     * @return \Entity\Project[]
     */
    public function getEnvironments()
    {
        return $this->getRepository()
            ->getItemsQuery()
            ->getQuery()
            ->getResult();
    }

    public function save(Project $project, Environment $environment)
    {
        if ($environment->getId() === null) {
            $environment->setProject($project);
            $environment->setEnabled(true);
            $this->entityManager->persist($environment);
        }

        $this->entityManager->flush($environment);
    }

}