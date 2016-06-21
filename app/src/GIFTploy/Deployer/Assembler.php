<?php

namespace GIFTploy\Deployer;

use Entity\Environment;
use GIFTploy\Filesystem\FilesystemBuilder;
use GIFTploy\Filesystem\ServerInterface;
use GIFTploy\Git\Commit;
use GIFTploy\Git\Diff;
use GIFTploy\Git\Parser\DiffParser;
use GIFTploy\Git\Parser\LogParser;
use GIFTploy\Git\Repository;

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

    public function __construct(
        Environment $environment,
        ServerInterface $server,
        Repository $workingTree
    ) {
        $this->environment = $environment;
        $this->server = $server;
        $this->workingTree = $workingTree;
        $this->deployer = new Deployer(new FilesystemBuilder($workingTree, $server));
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

    public function getFilesFromDiff(array $diffFiles, Commit $firstCommit = null)
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

        if ($firstCommit !== null) {
            $filesDiff = array_diff($firstCommit->getFiles()['create'], $files['delete']);
            $files['copy'] = array_unique(array_merge($files['copy'], $filesDiff));
            $files['delete'] = [];
        }

        return $files;
    }

    public function getDiffFileStack($commitHash)
    {
        $firstCommit = $this->getWorkingTree()->getFirstCommit(new LogParser());
        $lastDeployedRevision = $this->deployer->fetchLastDeployedRevision();

        $diff = new Diff($this->getWorkingTree(), new DiffParser());
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
