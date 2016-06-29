<?php

namespace Service;

use Doctrine\ORM\EntityManager;
use Entity\Environment;
use Entity\Server;
use Entity\ServerFtp;
use Form\ServerFtpFormType;
use GIFTploy\Filesystem\ServerInterface;

class ServerService
{

    const SERVER_TYPE_FTP = 'ftp';

    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return \Entity\ServerRepository
     */
    private function getRepository()
    {
        return $this->entityManager->getRepository(Server::class);
    }

    /**
     * @param int $id
     * @return \Entity\Server|null
     */
    public function findById($id)
    {
        return $this->entityManager->find(Server::class, $id);
    }

    /**
     * @param int $environmentId
     * @return \Entity\Server|null
     */
    public function getDefault($environmentId)
    {
        return $this->getRepository()->getDefault((int)$environmentId);
    }

    /**
     * Finds and returns right type of server instance.
     * If $typeId is null, empty instance is returned.
     *
     * @param string $type Type of server
     * @param integer $typeId Server ID
     * @return \GIFTploy\Filesystem\ServerInterface|null
     */
    public function getServerByType($type, $typeId = null)
    {
        $repositoryName = $this->resolveRepositoryByType($type);

        if ($repositoryName !== null) {
            $respository = $this->entityManager
                ->getRepository($repositoryName)
                ->find((int)$typeId);

            return ($respository !== null ? $respository : new $repositoryName());
        }

        return null;
    }

    public function getRepositoryByType($type)
    {
        $repositoryName = $this->resolveRepositoryByType($type);

        if ($repositoryName !== null) {
            return $this->entityManager->getRepository($repositoryName);
        }

        return null;
    }

    /**
     * @param string $type
     * @return \Symfony\Component\Form\FormTypeInterface|null
     */
    public function getFormByType($type)
    {
        $formName = null;

        switch ($type) {
            case self::SERVER_TYPE_FTP:
                $formName = ServerFtpFormType::class;
                break;
        }

        if ($formName !== null) {
            return new $formName();
        }

        return null;
    }

    private function resolveRepositoryByType($type)
    {
        switch ($type) {
            case self::SERVER_TYPE_FTP:
                return ServerFtp::class;
        }

        return null;
    }

    public function save(Environment $environment, ServerInterface $serverType, $isDefault = true)
    {
        $this->entityManager->beginTransaction();

        try {
            if ($serverType->getId() === null) {
                $serverType->setEnabled(true);
                $this->entityManager->persist($serverType);
                $this->entityManager->flush($serverType);

                $server = new Server();
                $server->setEnvironment($environment);
                $server->setType($serverType->getType());
                $server->setTypeId($serverType->getId());
                $server->setDefault($isDefault);

                $this->entityManager->persist($server);
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
            
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

}