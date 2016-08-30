<?php

namespace Controller;

use Form\EnvironmentFormType;
use Form\ProjectFormType;
use GIFTploy\Git\Commit;
use GIFTploy\Git\Diff;
use Silicone\Route;
use Silicone\Controller;
use GIFTploy\Git\Git;
use GIFTploy\Git\Parser\LogParser;
use GIFTploy\Git\Parser\DiffParser;
use GIFTploy\Filesystem\FilesystemBuilder;
use GIFTploy\Deployer\Deployer;
use Symfony\Component\Form\FormError;

/**
 * @Route("/project")
 */
class ProjectController extends Controller
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
     * @Route("/new-project", name="project-new")
     * @Route("/edit-project/{id}", name="project-edit", requirements={"id"="\d+"})
     */
    public function projectForm($id = null)
    {
        $project = $this->projectService->findById((int)$id);
        $editing = ($project !== null);
        $redirect = null;

        $actionUrl = $editing
            ? $this->url('project-edit', ['id' => $id])
            : $this->url('project-new');
        $form = $this->app->formType(new ProjectFormType(), $project, [
            'action' => $actionUrl,
        ]);

        if ($this->request->isMethod('POST')) {
            $form->submit($this->request);

            if ($form->isValid()) {
                try {
                    $project = $form->getData();
                    $this->projectService->save($project);

                    if ($editing) {
                        $this->successMessage($this->trans('form.project.successMessageEdit'));
                    } else {
                        $this->successMessage($this->trans('form.project.successMessageNew'));
                    }

                    $redirect = $this->app->url('project-list');

                } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                    $form->get('title')->addError(new FormError($this->trans('form.project.errorUniqueMessage')));

                } catch (\Exception $e) {
                    $this->errorMessage($this->trans('form.project.errorMessage'));
                }
            }
        }

        $response = $this->render('Project/project-form.twig', [
            'form' => $form->createView(),
            'editing' => $editing,
        ]);

        $response->setSharedMaxAge(5);

        return $this->json([
            'html' => $response->getContent(),
            'redirect' => $redirect,
        ]);
    }

    /**
     * @Route("/{projectId}/new-environment", name="environment-new", requirements={"projectId"="\d+"})
     * @Route("/{projectId}/edit-environment/{id}", name="environment-edit", requirements={"projectId"="\d+", "id"="\d+"})
     */
    public function environmentForm($projectId, $id = null)
    {
        $project = $this->projectService->findById((int)$projectId);
        $environment = $this->environmentService->findById((int)$id);
        $editing = ($environment !== null);
        $redirect = null;

        if ($environment !== null && $environment->getProject()->getId() !== $project->getId()) {
            $this->app->abort(404, $this->trans('error.404.environment'));
        }

        $actionUrl = $editing
            ? $this->url('environment-edit', [
                'id' => $id,
                'projectId' => $projectId,
            ])
            : $this->url('environment-new', [
                'projectId' => $projectId,
            ]);
        $form = $this->app->formType(new EnvironmentFormType($project), $environment, [
            'action' => $actionUrl,
        ]);

        if ($this->request->isMethod('POST')) {
            $form->submit($this->request);

            if ($form->isValid()) {
                $environment = $form->getData();

                try {
                    $this->environmentService->save($project, $environment);

                    if ($editing) {
                        $this->successMessage($this->trans('form.environment.successMessageEdit'));
                    } else {
                        $this->successMessage($this->trans('form.environment.successMessageNew'));
                    }

                    $redirect = $this->url('environment-show', [
                        'projectId' => $project->getId(),
                        'environmentId' => $environment->getId(),
                    ]);

                } catch (\Exception $e) {
                    $this->errorMessage($this->trans('form.environment.errorMessage'));
                }
            }
        }

        $response = $this->render('Project/environment-form.twig', [
            'form' => $form->createView(),
            'editing' => $editing,
        ]);

        $response->setSharedMaxAge(5);

        return $this->json([
            'html' => $response->getContent(),
            'redirect' => $redirect,
        ]);
    }

    /**
     * @Route("/{projectId}/environment/{environmentId}", name="environment-show", requirements={"projectId"="\d+", "environmentId"="\d+"})
     */
    public function showEnvironment($projectId, $environmentId)
    {
        $project = $this->projectService->findById((int)$projectId);
        $environment = $this->environmentService->findById((int)$environmentId);

        if ($project === null) {
            $this->app->abort(404, $this->trans('error.404.project'));
        }

        if (!($environment !== null && $environment->getProject()->getId() === $project->getId())) {
            $this->app->abort(404, $this->trans('error.404.environment'));
        }

        $repository = Git::getRepository($this->app->getProjectsDir().$environment->getDirectory());

        if (!$repository) {
            return $this->render('Project/clone-environment.twig', [
                'project' => $project,
                'environment' => $environment,
            ]);
        }

        $serverDefault = $this->serverService->getDefault($environment->getId());
        $serverType = $serverDefault !== null
            ? $this->serverService->getServerByType($serverDefault->getType(), $serverDefault->getTypeId())
            : null;
        $lastDeployedRevision = null;

        if ($serverType) {
            $deployer = new Deployer(new FilesystemBuilder($repository, $serverType));
            $lastDeployedRevision = $deployer->fetchLastDeployedRevision();
        }

        $commits = $repository->getLog(new LogParser())->getCommits();

        $deployUrlPrepared = ($serverType !== null ? $this->url('deploy', [
            'environmentId' => $environment->getId(),
            'serverId' => $serverDefault->getId(),
            'commitHash' => 'commitHash',
        ]) : null);

        $markUrlPrepared = ($serverType !== null ? $this->url('mark', [
            'environmentId' => $environment->getId(),
            'serverId' => $serverDefault->getId(),
            'commitHash' => 'commitHash',
        ]) : null);

        return $this->render('Project/show-environment.twig', [
            'project' => $project,
            'environment' => $environment,
            'repository' => $repository,
            'commits' => $commits,
            'firstCommit' => current($commits),
            'server' => $serverDefault,
            'serverType' => $serverType,
            'lastDeployedRevision' => $lastDeployedRevision,
            'deployUrlPrepared' => $deployUrlPrepared,
            'markUrlPrepared' => $markUrlPrepared,
        ]);
    }

    public function showCommitDetail(Commit $commit)
    {
        return $this->render('Project/_show-commit-detail.twig', [
            'commit' => $commit,
        ]);
    }

    /**
     * @Route("/show-diff/{environmentId}/{commitHashFrom}/{commitHashTo}", name="show-diff", requirements={"environmentId"="\d+"})
     */
    public function showDiff($environmentId, $commitHashFrom, $commitHashTo)
    {
        $environment = $this->environmentService->findById((int)$environmentId);
        $repository = Git::getRepository($this->app->getProjectsDir().$environment->getDirectory());

        $diff = new Diff($repository, new DiffParser());

        $diffFiles = $diff->setCommitHashFrom($commitHashFrom)
            ->setCommitHashTo($commitHashTo)
            ->getFiles([], true);

        $response = $this->render('Project/_show-diff.twig', [
            'diffFiles' => $diffFiles,
        ]);

        return $this->app->json(['html' => $response->getContent()]);
    }

}
