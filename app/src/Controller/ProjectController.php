<?php

namespace Controller;

use Entity\Environment;
use Entity\Project;
use Form\EnvironmentFormType;
use Form\ProjectFormType;
use GIFTploy\Git\Commit;
use GIFTploy\Git\Diff;
use Silicone\Route;
use Silicone\Controller;
use GIFTploy\Git\Git;
use GIFTploy\Git\Parser\LogParser;
use GIFTploy\Git\Parser\DiffParser;
use GIFTploy\Filesystem\ServerFactory;
use GIFTploy\ProcessConsole;
use GIFTploy\Filesystem\FilesystemBuilder;
use GIFTploy\Deployer\Deployer;

/**
 * @Route("/project")
 */
class ProjectController extends Controller
{

    /**
     * @Route("/new-project", name="project-new")
     * @Route("/edit-project/{id}", name="project-edit", requirements={"id"="\d+"})
     */
    public function projectform($id = null)
    {
        $project = $this->app->entityManager()->getRepository(Project::class)->find(intval($id));

        if (!$project) {
            $project = new Project();
            $project->setEnabled(true);
        }

        $form = $this->app->formType(new ProjectFormType(), $project);

        if ($this->request->isMethod('POST')) {
            $form->bind($this->request);

            if ($form->isValid()) {

                $project = $form->getData();

                $this->app->entityManager()->persist($project);
                $this->app->entityManager()->flush();

                return $this->app->redirect($this->app->url('login'));
            }
        }

        $response = $this->render('Project/project-form.twig', [
            'form' => $form->createView(),
        ]);

        $response->setSharedMaxAge(5);

        return $response;
    }

    /**
     * @Route("/{projectId}/new-environment", name="environment-new", requirements={"projectId"="\d+"})
     * @Route("/{projectId}/edit-environment/{id}", name="environment-edit", requirements={"projectId"="\d+", "id"="\d+"})
     */
    public function environmentForm($projectId, $id = null)
    {
        $project = $this->app->entityManager()->getRepository(Project::class)->find(intval($projectId));
        $environment = $this->app->entityManager()->getRepository(Environment::class)->find(intval($id));

        if (!$environment) {
            $environment = new Environment();
            $environment->setProject($project);
            $environment->setEnabled(true);
        }

        $form = $this->app->formType(new EnvironmentFormType(), $environment);

        if ($this->request->isMethod('POST')) {
            $form->bind($this->request);

            if ($form->isValid()) {

                $environment = $form->getData();

                $this->app->entityManager()->persist($environment);
                $this->app->entityManager()->flush();

                return $this->app->redirect($this->app->url('login'));
            }
        }

        $response = $this->render('Project/environment-form.twig', [
            'form' => $form->createView(),
        ]);

        $response->setSharedMaxAge(5);

        return $response;
    }

    /**
     * @Route("/{projectId}/environment/{environmentId}", name="environment-show", requirements={"projectId"="\d+", "environmentId"="\d+"})
     */
    public function showEnvironment($projectId, $environmentId)
    {
        $project = $this->app->entityManager()->getRepository(Project::class)->find(intval($projectId));
        $environment = $this->app->entityManager()->getRepository(Environment::class)->find(intval($environmentId));

        if (!$project) {
            $this->app->abort(404, $this->app->trans('error.404.project'));
        }

        if (!$environment) {
            $this->app->abort(404, $this->app->trans('error.404.environment'));
        }

        $directory = $this->app->getProjectsDir().$environment->getDirectory();
        $repository = Git::getRepository($this->app->getProjectsDir().$environment->getDirectory());

        if (!$repository) {
            $repository = Git::cloneRepository($directory, $project->getUrl(), $environment->getBranch());
        }

        $serverDefault = $environment->getServers(1)->first();
        $server = ($serverDefault ? $serverDefault->getServer(new ServerFactory($this->app->entityManager())) : null);
        $lastDeployedRevision = null;

        if ($server) {
            $deployer = new Deployer(new FilesystemBuilder($repository, $server));
            $lastDeployedRevision = $deployer->fetchLastDeployedRevision();
        }

        $commits = $repository->getLog(new LogParser())->getCommits();

        $deployUrlPrepared = ($server !== null ? $this->app->url('deploy', [
            'environmentId' => $environment->getId(),
            'serverFactoryId' => $serverDefault->getId(),
            'commitHash' => 'commitHash',
        ]) : null);

        $markUrlPrepared = ($server !== null ? $this->app->url('mark', [
            'environmentId' => $environment->getId(),
            'serverFactoryId' => $serverDefault->getId(),
            'commitHash' => 'commitHash',
        ]) : null);

        return $this->render('Project/show-environment.twig', [
            'project' => $project,
            'environment' => $environment,
            'repository' => $repository,
            'commits' => $commits,
            'firstCommit' => current($commits),
            'serverFactory' => $serverDefault,
            'server' => $server,
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
        $environmentObj = $this->app->entityManager()->getRepository(Environment::class)->find(intval($environmentId));
        $repository = Git::getRepository($this->app->getProjectsDir().$environmentObj->getDirectory());

        $diff = new Diff($repository, new DiffParser());

        $diffFiles = $diff->setCommitHashFrom($commitHashFrom)
            ->setCommitHashTo($commitHashTo)
            ->getFiles([], true);

        $response = $this->render('Project/_show-diff.twig', [
            'diffFiles' => $diffFiles,
        ]);

        return $this->app->json(['html' => $response->getContent()]);
    }

    /**
     * @Route("/clone/{projectId}", name="project-clone", requirements={"id"="\d+"})
     */
    public function cloneRepository($projectId)
    {
        $p = Git::cloneRepository('c:/www/aaaaaaaaaaaaaaaaaaaaa/', 'https://github.com/jasny/bootstrap.git', [], new ProcessConsole());
        d($p);

        dd($this->request);
    }
}
