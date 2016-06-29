<?php

namespace Controller;

use League\Flysystem\Filesystem;
use Silicone\Route;
use Silicone\Controller;

/**
 * @Route("/server")
 */
class ServerController extends Controller
{

    /** @var \Service\ServerService */
    private $serverService;

    /** @var \Service\EnvironmentService */
    private $environmentService;

    public function __construct(\Application $app)
    {
        parent::__construct($app);

        $this->serverService = $app['ServerService'];
        $this->environmentService = $app['EnvironmentService'];
    }

    /**
     * @Route("/{environmentId}/new-server/{type}", name="server-new", requirements={"environmentId"="\d+","type"="[a-z]+"})
     * @Route("/{environmentId}/edit-server/{type}/{serverId}", name="server-edit", requirements={"serverId"="\d+", "environmentId"="\d+","type"="[a-z]+"})
     */
    public function serverform($environmentId, $type, $serverId = null)
    {
        $environment = $this->environmentService->findById((int)$environmentId);
        $server = $this->serverService->findById((int)$serverId);

        if (!$environment) {
            $this->app->abort(404, $this->app->trans('error.404.environment'));
        }

        if ($server !== null && $server->getEnvironment()->getId() !== $environment->getId()) {
            $this->app->abort(404, $this->app->trans('error.404.server'));
        }

        $serverTypeId = ($server !== null ? $server->getTypeId() : null);
        $serverType = $this->serverService->getServerByType($type, $serverTypeId);
        $editing = ($serverType !== null);
        $form = $this->app->formType($this->serverService->getFormByType($type), $serverType);

        if ($this->request->isMethod('POST')) {
            $form->submit($this->request);

            if ($form->isValid()) {
                try {
                    $serverType = $form->getData();

                    // try connect
                    $serverType->getAdapter()->getConnection();

                    $defaultServer = $this->serverService->getDefault($environment->getId());
                    $isDefault = ($defaultServer === null);

                    $this->serverService->save($environment, $serverType, $isDefault);

                    if ($editing) {
                        $this->successMessage($this->trans('form.server.successMessageEdit'));
                    } else {
                        $this->successMessage($this->trans('form.server.successMessageNew'));
                    }

                    return $this->app->redirect($this->url('environment-show', [
                        'projectId' => $environment->getProject()->getId(),
                        'environmentId' => $environment->getId(),
                    ]));

                } catch (\RuntimeException $e) {
                    $this->errorMessage($this->trans('form.server.errorMessageConnect'));

                } catch (\Exception $e) {
                    ddd($e);
                    $this->errorMessage($this->trans('form.server.errorMessage'));
                }
            }
        }

        $response = $this->render('Server/server-ftp-form.twig', [
            'form' => $form->createView(),
        ]);

        $response->setSharedMaxAge(5);

        return $response;
    }
}
