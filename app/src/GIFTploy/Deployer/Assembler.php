<?php

namespace GIFTploy\Deployer;

/**
 * Description of Assembler
 *
 * @author Patrik Chotěnovský
 */
class Assembler
{
    private $environment;
    private $server;
    private $workingTree;

    /**
     * @var \GIFTploy\Deployer\Deployer
     */
    private $deployer;

    public function __construct(\Entity\Environment $environment, \GIFTploy\Filesystem\ServerInterface $server, \GIFTploy\Git\Repository $workingTree)
    {
        $this->environment = $environment;
        $this->server = $server;
        $this->workingTree = $workingTree;
        $this->deployer = new Deployer(new \GIFTploy\Filesystem\FilesystemBuilder($workingTree, $server));
    }

    public function getEnvironment()
    {
        return $this->environment;
    }

    public function getServer()
    {
        return $this->server;
    }

    public function getWorkingTree()
    {
        return $this->workingTree;
    }

    public function getDeployer()
    {
        return $this->deployer;
    }

    public function getFilesFromDiff(array $diffFiles, \GIFTploy\Git\Commit $firstCommit = null)
    {
        $files = [
            'copy' => [],
            'delete' => [],
        ];

        foreach ($diffFiles as $file) {
            if ($file['mode'] == 'delete') {
                $files['delete'][] = $file['filename'];
            } else {
                $files['copy'][] = $file['filename'];
            }
        }

        if ($firstCommit !== null)
        {
            $filesDiff = array_diff($firstCommit->getFiles()['create'], $files['delete']);
            $files['copy'] = array_unique(array_merge($files['copy'], $filesDiff));
            $files['delete'] = [];
        }

        return $files;
    }

    public function getDiffFileStack($commitHash)
    {
        $firstCommit = $this->getWorkingTree()->getFirstCommit(new \GIFTploy\Git\Parser\LogParser());
        $lastDeployedRevision = $this->deployer->fetchLastDeployedRevision();

        $diff = new \GIFTploy\Git\Diff($this->getWorkingTree(), new \GIFTploy\Git\Parser\DiffParser());
        $diffFiles = $diff->setCommitHashFrom(($lastDeployedRevision ? $lastDeployedRevision : $firstCommit->getCommitHash()))
            ->setCommitHashTo($commitHash)
            ->getFiles([], true);

        $files = $this->getFilesFromDiff($diffFiles, (!$lastDeployedRevision ? $firstCommit : null));

        $fileStack = new FileStack();

        foreach ($files as $mode => $fileMode) {
            foreach ($fileMode as $file) {
                if ($mode == 'copy') {
                    $fileStack->copy($file);
                } else {
                    $fileStack->delete($file);
                }
            }
        }

        return $fileStack;
    }
}
