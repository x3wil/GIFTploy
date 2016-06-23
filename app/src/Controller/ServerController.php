<?php

namespace Controller;

use Entity\Environment;
use Entity\ServerFtp;
use Form\ServerFtpFormType;
use Silicone\Route;
use Silicone\Controller;

/**
 * @Route("/server")
 */
class ServerController extends Controller
{

    /**
     * @Route("/{environmentId}/new-ftp-server", name="server-ftp-new", requirements={"environmentId"="\d+"})
     * @Route("/{environmentId}/edit-ftp-server/{id}", name="server-ftp-edit", requirements={"id"="\d+", "environmentId"="\d+"})
     */
    public function ftpform($environmentId, $id = null)
    {
        $environmentObj = $this->app->entityManager()->getRepository(Environment::class)->find(intval($environmentId));

        if (!$environmentObj) {
            $this->app->abort(404, $this->app->trans('error.404.environment'));
        }

        $serverFtpObj = $this->app->entityManager()->getRepository(ServerFtp::class)->find(intval($id));

        if (!$serverFtpObj) {
            $serverFtpObj = new ServerFtp();
            $serverFtpObj->setEnabled(true);
        }

        $form = $this->app->formType(new ServerFtpFormType(), $serverFtpObj);

        if ($this->request->isMethod('POST')) {
            $form->bind($this->request);

            if ($form->isValid()) {

                $repositoryObj = $form->getData();

                $this->app->entityManager()->persist($repositoryObj);
                $this->app->entityManager()->flush();

                return $this->app->redirect($this->app->url('login'));
            }
        }

        $response = $this->render('Server/server-ftp-form.twig', [
            'form' => $form->createView(),
        ]);

        $response->setSharedMaxAge(5);

        return $response;
    }
}
