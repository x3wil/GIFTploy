<?php

namespace Controller;

use Silicone\Route;
use Silicone\Controller;
use GIFTploy\Git\Git;
use GIFTploy\Deployer\Deployer;
use GIFTploy\Filesystem\FilesystemBuilder;
use GIFTploy\Filesystem\ServerFactory;
use GIFTploy\Git\Parser\DiffParser;
use GIFTploy\Git\Parser\LogParser;
use GIFTploy\Deployer\FileStack;

/**
 * @Route("/process")
 */
class ProcessController extends Controller
{

    /**
     * @Route("/deploy/{environmentId}/{serverFactoryId}/{commitHash}", name="deploy", requirements={"environmentId"="\d+", "serverFactoryId"="\d+"})
     */
    public function deploy($environmentId, $serverFactoryId, $commitHash)
    {
        $environmentObj = $this->app->entityManager()->getRepository('Entity\Environment')->find(intval($environmentId));
        $gitRepository = Git::getRepository($this->app->getProjectsDir().$environmentObj->getDirectory());
        $firstCommit = $gitRepository->getFirstCommit(new LogParser());

        $serverFactory = $this->app->entityManager()
            ->getRepository('Entity\ServerFactory')
            ->findOneBy([
                'id' => $serverFactoryId,
                'environment' => $environmentId,
            ]);

        $server = $serverFactory->getServer(new ServerFactory($this->app->entityManager()));
        
        $deployer = new Deployer(new FilesystemBuilder($gitRepository, $server));
        $lastDeployedRevision = $deployer->fetchLastDeployedRevision();

        $diff = new \GIFTploy\Git\Diff($gitRepository, new DiffParser());
        $diffFiles = $diff->setCommitHashFrom(($lastDeployedRevision ? $lastDeployedRevision : $firstCommit->getCommitHash()))
            ->setCommitHashTo($commitHash)
            ->getFiles([], true);

        if (!$lastDeployedRevision) {
            $gitRepository->getFirstCommit(new LogParser());
        }

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

        $gitRepository->checkout($commitHash);

        $deployer->deploy($fileStack, function($file, $result, $errorMessage) {
        });

        $gitRepository->checkout($environmentObj->getBranch());

        $deployer->writeLastDeployedRevision($commitHash);

        return $this->app->redirect($this->app->url('environment-show', ['repositoryId' => $environmentObj->getRepository()->getId(), 'environmentId' => $environmentObj->getId()]));
    }
    
    protected function getFilesFromDiff(array $diffFiles, \GIFTploy\Git\Commit $firstCommit = null)
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

}