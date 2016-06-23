<?php

namespace GIFTploy\Filesystem;

use Doctrine\ORM\EntityManager;
use Entity\ServerFtp;

/**
 * Factory class for server types.
 *
 * @author Patrik Chotěnovský
 */
class ServerFactory
{

    const SERVER_TYPE_FTP = 'ftp';
    const SERVER_TYPE_SFTP = 'sftp';

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Finds and returns right type of server instance.
     * If $typeId is null, empty instance is returned.
     *
     * @param string $type Type of server
     * @param integer $typeId Server ID
     * @return \GIFTploy\Filesystem\ServerInterface|null
     */
    public function create($type, $typeId = null)
    {
        switch ($type) {
            case self::SERVER_TYPE_FTP:
                $repositoryName = ServerFtp::class;
                break;
        }

        if (isset($repositoryName)) {
            $respository = $this->entityManager
                ->getRepository($repositoryName)
                ->find($typeId);

            return ($respository ? $respository : new $repositoryName());
        }

        return null;
    }

}
