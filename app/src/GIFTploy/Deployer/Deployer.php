<?php

namespace GIFTploy\Deployer;

/**
 * Class for managing deployment and revision controll on server.
 *
 * @author Patrik Chotěnovský
 */
class Deployer
{

    const LAST_DEPLOY_FILE = '.giftploy';

    /**
     * @var \GIFTploy\Filesystem\FilesystemBuilder
     */
    protected $filesystemBuilder;

    /**
     * @var \League\Flysystem\MountManager
     */
    protected $manager;

    /**
     * @var array
     */
    protected $files = [];

    /**
     * Constructs deployer instance and sets manager.
     *
     * @param \GIFTploy\Filesystem\FilesystemBuilder $filesystemBuilder
     */
    public function __construct(\GIFTploy\Filesystem\FilesystemBuilder $filesystemBuilder)
    {
        $this->filesystemBuilder = $filesystemBuilder;
        $this->manager = $filesystemBuilder->getManager();
    }

    /**
     * Returns last deployed revision from .giftploy file on server.
     *
     * @return string
     */
    public function fetchLastDeployedRevision()
    {
        try {
            $revision = $this->manager->read('remote://'.self::LAST_DEPLOY_FILE);
        } catch (\League\Flysystem\FileNotFoundException $e) {
            $revision = null;
        }

        return $revision;
    }

    /**
     * Writes deployed revision to .giftploy file on server.
     *
     * @return boolean
     */
    public function writeLastDeployedRevision($commitHash)
    {
        return $this->manager->put('remote://'.self::LAST_DEPLOY_FILE, $commitHash);
    }

    /**
     * Process deployment. Gets stack o files and copy/delete them to/from server.
     * Callback function returns filename, copy/delete result and error message.
     *
     * @param \GIFTploy\Deployer\FileStack $fileStack
     * @param function $callback
     */
    public function deploy(FileStack $fileStack, $callback = null)
    {
        $errorMessage = '';

        foreach ($fileStack->getFiles() as $mode => $files) {

            foreach ($files as $file) {
                if ($mode == 'copy') {
                    $result = $this->copyToRemote('/'.$file, $errorMessage);
                } else {
                    $result = $this->deleteFromRemote('/'.$file, $errorMessage);
                }

                if (is_callable($callback)) {
                    $callback($file, $result, $errorMessage);
                }
            }
        }
    }

    /**
     * Copy file to server.
     *
     * @param string $file
     * @param string $error     Passed by reference.
     * @return boolean
     */
    protected function copyToRemote($file, &$error = null)
    {
        try {
            return $this->manager->put('remote://'.$file, $this->manager->read('local://'.$file));
        } catch (\League\Flysystem\FileNotFoundException $e) {
            $error = $e->getMessage();
            return false;
        }
    }

    /**
     * Delete file from server.
     *
     * @param type $file
     * @param type $error     Passed by reference.
     * @return boolean
     */
    protected function deleteFromRemote($file, &$error = null)
    {
        try {
            return $this->manager->delete('remote://'.$file);
        } catch (\League\Flysystem\FileNotFoundException $e) {
            $error = $e->getMessage();
            return false;
        }
    }

}
