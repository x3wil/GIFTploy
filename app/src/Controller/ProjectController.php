<?php

namespace Controller;

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
    public function repositoryform($id = null)
    {
        $repositoryObj = $this->app->entityManager()->getRepository('Entity\Repository')->find(intval($id));

        if (!$repositoryObj) {
            $repositoryObj = new \Entity\Repository();
            $repositoryObj->setEnabled(true);
        }

        $form = $this->app->formType(new \Form\RepositoryFormType(), $repositoryObj);

        if ($this->request->isMethod('POST')) {
            $form->bind($this->request);

            if ($form->isValid()) {

                $repositoryObj = $form->getData();

                $this->app->entityManager()->persist($repositoryObj);
                $this->app->entityManager()->flush();

                return $this->app->redirect($this->app->url('login'));
            }
        }

        $response = $this->render('Repository/repository-form.twig', [
            'form' => $form->createView(),
        ]);

        $response->setSharedMaxAge(5);
        return $response;
    }

    /**
     * @Route("/{repositoryId}/new-environment", name="environment-new", requirements={"repositoryId"="\d+"})
     * @Route("/{repositoryId}/edit-environment/{id}", name="environment-edit", requirements={"repositoryId"="\d+", "id"="\d+"})
     */
    public function environmentForm($repositoryId, $id = null)
    {
        $repositoryObj = $this->app->entityManager()->getRepository('Entity\Repository')->find(intval($repositoryId));
        $environmentObj = $this->app->entityManager()->getRepository('Entity\Environment')->find(intval($id));

        if (!$environmentObj) {
            $environmentObj = new \Entity\Environment();
            $environmentObj->setRepository($repositoryObj);
            $environmentObj->setEnabled(true);
        }

        $form = $this->app->formType(new \Form\EnvironmentFormType(), $environmentObj);

        if ($this->request->isMethod('POST')) {
            $form->bind($this->request);

            if ($form->isValid()) {

                $environmentObj = $form->getData();

                $this->app->entityManager()->persist($environmentObj);
                $this->app->entityManager()->flush();

                return $this->app->redirect($this->app->url('login'));
            }
        }

        $response = $this->render('Repository/environment-form.twig', [
            'form' => $form->createView(),
        ]);

        $response->setSharedMaxAge(5);
        return $response;
    }

    /**
     * @Route("/{repositoryId}/environment/{environmentId}", name="environment-show", requirements={"repositoryId"="\d+", "environmentId"="\d+"})
     */
    public function showEnvironment($repositoryId, $environmentId)
    {
        $repositoryObj = $this->app->entityManager()->getRepository('Entity\Repository')->find(intval($repositoryId));
        $environmentObj = $this->app->entityManager()->getRepository('Entity\Environment')->find(intval($environmentId));

        if (!$repositoryObj) {
            $this->app->abort(404, $this->app->trans('error.404.repository'));
        }

        if (!$environmentObj) {
            $this->app->abort(404, $this->app->trans('error.404.environment'));
        }

        $directory = $this->app->getProjectsDir().$environmentObj->getDirectory();
        $gitRepository = Git::getRepository($this->app->getProjectsDir().$environmentObj->getDirectory());

        if (!$gitRepository) {
            $gitRepository = Git::cloneRepository($directory, $repositoryObj->getUrl(), $environmentObj->getBranch());
        }

        $serverDefault = $environmentObj->getServers(1)->first();
        $server = ($serverDefault ? $serverDefault->getServer(new ServerFactory($this->app->entityManager())) : null);
        $lastDeployedRevision = null;

        if ($server) {
            $deployer = new Deployer(new FilesystemBuilder($gitRepository, $server));
            $lastDeployedRevision = $deployer->fetchLastDeployedRevision();
        }

        $commits = $gitRepository->getLog(new LogParser())->getCommits([], true);

        $deployUrlPrepared = ($server !== null ? $this->app->url('deploy', [
            'environmentId' => $environmentObj->getId(),
            'serverFactoryId' => $serverDefault->getId(),
            'commitHash' => 'commitHash',
        ]) : null);

        $markUrlPrepared = ($server !== null ? $this->app->url('mark', [
            'environmentId' => $environmentObj->getId(),
            'serverFactoryId' => $serverDefault->getId(),
            'commitHash' => 'commitHash',
        ]) : null);

        return $this->render('Repository/show-environment.twig', [
            'repositoryObj' => $repositoryObj,
            'environmentObj' => $environmentObj,
            'gitRepository' => $gitRepository,
            'commits' => $commits,
            'firstCommit' => current($commits),
            'serverFactory' => $serverDefault,
            'server' => $server,
            'lastDeployedRevision' => $lastDeployedRevision,
            'deployUrlPrepared' => $deployUrlPrepared,
            'markUrlPrepared' => $markUrlPrepared,
        ]);
    }

    public function showCommitDetail(\GIFTploy\Git\Commit $commit)
    {
        return $this->render('Repository/_show-commit-detail.twig', [
            'commit' => $commit,
        ]);
    }

    /**
     * @Route("/show-diff/{environmentId}/{commitHashFrom}/{commitHashTo}", name="show-diff", requirements={"environmentId"="\d+"})
     */
    public function showDiff($environmentId, $commitHashFrom, $commitHashTo)
    {
        $environmentObj = $this->app->entityManager()->getRepository('Entity\Environment')->find(intval($environmentId));
        $gitRepository = Git::getRepository($this->app->getProjectsDir().$environmentObj->getDirectory());

        $diff = new \GIFTploy\Git\Diff($gitRepository, new DiffParser());
        
        $diffFiles = $diff->setCommitHashFrom($commitHashFrom)
            ->setCommitHashTo($commitHashTo)
            ->getFiles([], true);

        $response = $this->render('Repository/_show-diff.twig', [
            'diffFiles' => $diffFiles,
        ]);

        return $this->app->json(['html' => $response->getContent()]);
    }

    /**
     * @Route("/clone/{repositoryId}", name="repository-clone", requirements={"id"="\d+"})
     */
    public function cloneRepository($repositoryId)
    {
//        ddd($repositoryId, $this->request->get("console"));

        $p = Git::cloneRepository('c:/www/aaaaaaaaaaaaaaaaaaaaa/', 'https://github.com/jasny/bootstrap.git', [], [], new ProcessConsole);
        d($p);

        dd($this->request);
    }
}
