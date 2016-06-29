<?php
namespace Controller;

use Silicone\Route;
use Silicone\Controller;

class IndexController extends Controller
{

    /** @var \Service\ProjectService */
    private $projectService;

    public function __construct(\Application $app)
    {
        parent::__construct($app);

        $this->projectService = $app['ProjectService'];
    }

    /**
     * @Route("/", name="project-list")
     */
    public function projectList()
    {
        return $this->render('Default/project-list.twig', [
            'projects' => $this->projectService->getProjects(),
        ]);
    }

    /**
     * @Route("/process-console", name="process-console")
     */
    public function consoleOutputAction()
    {
        $url = $this->request->get('url', null);
        $title = $this->request->get('title', null);

        return $this->render("Default/console.twig", [
            "url" => $url,
            "title" => $title,
        ]);
    }
}