<?php

namespace GIFTploy\Filesystem;

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
    public function __construct(\GIFTploy\Git\Repository $repository, ServerInterface $server)
    {
        $this->repository = $repository;
        $this->server = $server;
        
        $this->localAdapter = new \League\Flysystem\Adapter\Local($repository->getDir());
        $this->remoteAdapter = $server->getAdapter();
    }

    /**
     * Returns mount manager wrapper with local and remote filesystems
     *
     * @return \League\Flysystem\MountManager
     */
    public function getManager()
    {
        return new \League\Flysystem\MountManager([
            'local' => new \League\Flysystem\Filesystem($this->localAdapter),
            'remote' => new \League\Flysystem\Filesystem($this->remoteAdapter),
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
     * @return ServerInterface
     */
    public function getServer()
    {
        return $this->server;
    }

}
