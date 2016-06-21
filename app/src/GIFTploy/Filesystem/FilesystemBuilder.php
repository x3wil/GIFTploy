<?php

namespace GIFTploy\Filesystem;

use GIFTploy\Git\Repository;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;

/**
 * Class for creating Flysystem manager.
 *
 * @author Pat
 */
class FilesystemBuilder
{
    /**
     * @var \League\Flysystem\Adapter\Local
     */
    protected $localAdapter;

    /**
     * Flysystem adapter based on server type.
     *
     * @var mixed
     */
    protected $remoteAdapter;

    /**
     * @var \GIFTploy\Git\Repository
     */
    protected $repository;

    /**
     * @var ServerInterface
     */
    protected $server;

    /**
     * @param \GIFTploy\Git\Repository $repository
     * @param \GIFTploy\Filesystem\ServerInterface $server
     */
    public function __construct(Repository $repository, ServerInterface $server)
    {
        $this->repository = $repository;
        $this->server = $server;

        $this->localAdapter = new Local($repository->getDir());
        $this->remoteAdapter = $server->getAdapter();
    }

    /**
     * Returns mount manager wrapper with local and remote filesystems
     *
     * @return \League\Flysystem\MountManager
     */
    public function getManager()
    {
        return new MountManager([
            'local' => new Filesystem($this->localAdapter),
            'remote' => new Filesystem($this->remoteAdapter),
        ]);
    }

    /**
     * Return instance of git repository.
     *
     * @return \GIFTploy\Git\Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Returns entity instance of server.
     *
     * @return \GIFTploy\Filesystem\ServerInterface
     */
    public function getServer()
    {
        return $this->server;
    }

}
