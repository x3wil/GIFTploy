<?php

namespace Controller;

use Silicone\Route;
use Silicone\Controller;
use GIFTploy\Git\Git;
use GIFTploy\ProcessConsole;

/**
 * @Route("/project")
 */
class ProjectController extends Controller
{

    /**
     * @Route("/{id}", name="repository-show", requirements={"id"="\d+"})
     */
    public function show($id = null)
    {
        $repositoryObj = $this->app->entityManager()->getRepository('Entity\Repository')->find(intval($id));
        
        if (!$repositoryObj) {
            $this->app->abort(404, $this->app->trans('error.404.repository'));
        }

//        ddd($repositoryObj->getDirectory());

        return $this->render('Repository/show.twig', [
            'repositoryObj' => $repositoryObj,
        ]);
    }

    /**
     * @Route("/new-repository", name="repository-new")
     * @Route("/edit-repository/{id}", name="repository-edit", requirements={"id"="\d+"})
     */
    public function repositoryform($id = null)
    {
        $repositoryObj = $this->app->entityManager()->getRepository('Entity\Repository')->find(intval($id));
        $form = $this->app->formType(new \Form\RepositoryFormType(), $repositoryObj);
        
        if ($this->request->isMethod('POST')) {
            $form->bind($this->request);

            if ($form->isValid()) {

                $repositoryObj = $form->getData();

                if ($repositoryObj->isNew()) {
                    $repositoryObj->setEnabled(true);
                }

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
        }

        $form = $this->app->formType(new \Form\EnvironmentFormType(), $environmentObj);

        if ($this->request->isMethod('POST')) {
            $form->bind($this->request);

            if ($form->isValid()) {

                $environmentObj = $form->getData();

                if ($environmentObj->isNew()) {
                    $environmentObj->setEnabled(true);
                }

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
