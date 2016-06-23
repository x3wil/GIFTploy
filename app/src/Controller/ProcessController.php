<?php

namespace Controller;

use Entity\Environment;
use Entity\Project;
use GIFTploy\Deployer\Assembler;
use Silicone\Route;
use Silicone\Controller;
use GIFTploy\Git\Git;
use GIFTploy\Filesystem\ServerFactory;
use GIFTploy\ProcessConsole;
use Symfony\Component\HttpFoundation\Response;

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
        /* @var \Entity\Environment $environment */
        $environment = $this->app->entityManager()->getRepository(Environment::class)->find(intval($environmentId));
        $serverFactory = $this->app->entityManager()
            ->getRepository(\Entity\ServerFactory::class)
            ->findOneBy([
                'id' => $serverFactoryId,
                'environment' => $environmentId,
            ]);

        $server = $serverFactory->getServer(new ServerFactory($this->app->entityManager()));
        $workingTree = Git::getRepository($this->app->getProjectsDir().$environment->getDirectory());

        $assembler = new Assembler($environment, $server, $workingTree);
        $deployer = $assembler->getDeployer();
        $fileStack = $assembler->getDiffFileStack($commitHash);
        $console = new ProcessConsole(ProcessConsole::TYPE_MODAL);

        $workingTree->checkout($commitHash);
        $deployer->deploy($fileStack, function ($file, $mode, $result, $errorMessage) use ($console) {

            if ($result === null) {
                $msg = ($mode == 'delete' ? 'Deleting file: ' : 'Uploading file: ');
                $msg .= $file.'... ';

                $console->flushProgress($msg);

            } else {
                $console->flushResult($result, $errorMessage);
            }
        });

        $console->closeConsole();
        $workingTree->checkout($environment->getBranch());
        $deployer->writeLastDeployedRevision($commitHash);

        return new Response();
    }

    /**
     * @Route("/mark/{environmentId}/{serverFactoryId}/{commitHash}", name="mark", requirements={"environmentId"="\d+", "serverFactoryId"="\d+"})
     */
    public function mark($environmentId, $serverFactoryId, $commitHash)
    {
        /* @var \Entity\Environment $environment */
        $environment = $this->app->entityManager()->getRepository(Environment::class)->find(intval($environmentId));
        $serverFactory = $this->app->entityManager()
            ->getRepository(\Entity\ServerFactory::class)
            ->findOneBy([
                'id' => $serverFactoryId,
                'environment' => $environmentId,
            ]);

        $server = $serverFactory->getServer(new ServerFactory($this->app->entityManager()));
        $workingTree = Git::getRepository($this->app->getProjectsDir().$environment->getDirectory());

        $assembler = new Assembler($environment, $server, $workingTree);
        $deployer = $assembler->getDeployer();

        $deployer->writeLastDeployedRevision($commitHash);

        return $this->app->redirect($this->app->url('environment-show', [
            'repositoryId' => $environment->getProject()->getId(),
            'environmentId' => $environment->getId(),
        ]));
    }

    /**
     * @Route("/clone/{projectId}/{environmentId}", name="process-clone-repository", requirements={"projectId"="\d+", "environmentId"="\d+"})
     */
    public function cloneRepository($projectId, $environmentId)
    {
        /** @var \Entity\Project $project */
        $project = $this->app->entityManager()->getRepository(Project::class)->find(intval($projectId));
        /** @var \Entity\Environment $environment */
        $environment = $this->app->entityManager()->getRepository(Environment::class)->find(intval($environmentId));

        if (!$project) {
            $this->app->abort(404, $this->app->trans('error.404.project'));
        }

        if (!$environment) {
            $this->app->abort(404, $this->app->trans('error.404.environment'));
        }

        $directory = $this->app->getProjectsDir().$environment->getDirectory();
        Git::cloneRepository($directory, $project->getUrl(), $environment->getBranch(), new ProcessConsole(ProcessConsole::TYPE_INLINE));

        return new Response();
    }

}
