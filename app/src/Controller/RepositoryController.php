<?php

namespace Controller;

use Silicone\Route;
use Silicone\Controller;
use GIFTploy\Git;

/**
 * @Route("/repository")
 */
class RepositoryController extends Controller
{

    /**
     * @Route("/new", name="repository-new")
     * @Route("/edit/{id}", name="repository-edit", requirements={"id"="\d+"})
     */
    public function form($id = null)
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

        $response = $this->render('Repository/form.twig', [
            'form' => $form->createView(),
        ]);

        $response->setSharedMaxAge(5);
        return $response;
    }

}
