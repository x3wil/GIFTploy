<?php

namespace Controller;

use GIFTploy\Deployer\Assembler;
use Silicone\Route;
use Silicone\Controller;
use GIFTploy\Git\Git;
use GIFTploy\ProcessConsole;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/process")
 */
class ProcessController extends Controller
{

    /** @var \Service\ProjectService */
    private $projectService;

    /** @var \Service\EnvironmentService */
    private $environmentService;

    /** @var \Service\ServerService */
    private $serverService;

    public function __construct(\Application $app)
    {
        parent::__construct($app);

        $this->projectService = $app['ProjectService'];
        $this->environmentService = $app['EnvironmentService'];
        $this->serverService = $app['ServerService'];
    }

    /**
     * @Route("/deploy/{environmentId}/{serverId}/{commitHash}", name="deploy", requirements={"environmentId"="\d+", "serverId"="\d+"})
     */
    public function deploy($environmentId, $serverId, $commitHash)
    {
        $environment = $this->environmentService->findById((int)$environmentId);
        $server = $this->serverService->findById((int)$serverId);

        if ($server !== null && $server->getEnvironment()->getId() !== $environment->getId()) {
            $this->app->abort(404, $this->app->trans('error.404.server'));
        }

        $serverType = $this->serverService->getServerByType($server->getType(), $server->getTypeId());
        $workingTree = Git::getRepository($this->app->getProjectsDir().$environment->getDirectory());

        $assembler = new Assembler($environment, $serverType, $workingTree);
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
     * @Route("/mark/{environmentId}/{serverId}/{commitHash}", name="mark", requirements={"environmentId"="\d+", "serverId"="\d+"})
     */
    public function mark($environmentId, $serverId, $commitHash)
    {
        $environment = $this->environmentService->findById((int)$environmentId);
        $server = $this->serverService->findById((int)$serverId);

        if ($server !== null && $server->getEnvironment()->getId() !== $environment->getId()) {
            $this->app->abort(404, $this->app->trans('error.404.server'));
        }

        $serverType = $this->serverService->getServerByType($server->getType(), $server->getTypeId());
        $workingTree = Git::getRepository($this->app->getProjectsDir().$environment->getDirectory());

        $assembler = new Assembler($environment, $serverType, $workingTree);
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
        $project = $this->projectService->findById((int)$projectId);
        $environment = $this->environmentService->findById((int)$environmentId);

        if (!$project) {
            $this->app->abort(404, $this->app->trans('error.404.project'));
        }

        if (!($environment !== null && $environment->getProject()->getId() === $project->getId())) {
            $this->app->abort(404, $this->trans('error.404.environment'));
        }

        $directory = $this->app->getProjectsDir().$environment->getDirectory();
        Git::cloneRepository($directory, $project->getUrl(), $environment->getBranch(), new ProcessConsole(ProcessConsole::TYPE_INLINE));

        return new Response();
    }

}
