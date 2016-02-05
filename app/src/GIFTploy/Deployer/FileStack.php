<?php

namespace GIFTploy\Deployer;

/**
 * Class for stacking files to deploy.
 *
 * @author Patrik Chotěnovský
 */
class FileStack
{
    /**
     * @var array
     */
    protected $files = [];

    public function __construct()
    {
        $this->clearStack();
    }

    /**
     * Add filename to stack for copying.
     *
     * @param string $filename
     */
    public function copy($filename)
    {
        $this->files['copy'][] = $filename;
    }

    /**
     * Add filename to stack for deleting.
     *
     * @param string $filename
     */
    public function delete($filename)
    {
        $this->files['delete'][] = $filename;
    }

    /**
     * Clears stacked files.
     */
    public function clearStack()
    {
        $this->files = [
            'copy' => [],
            'delete' => [],
        ];
    }

    /**
     * Returns stacked files.
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }
}
