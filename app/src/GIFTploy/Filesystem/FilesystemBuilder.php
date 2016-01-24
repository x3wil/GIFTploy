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

    public function __construct(\GIFTploy\Git\Repository $repository, ServerInterface $server)
    {
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
}
