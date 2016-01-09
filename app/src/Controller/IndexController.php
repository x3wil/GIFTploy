<?php
namespace Controller;

use Silicone\Route;
use Silicone\Controller;

class IndexController extends Controller
{

    /**
     * @Route("/", name="project-list")
     */
    public function projectList()
    {
        $repository = $this->app->entityManager()->getRepository('Entity\Repository');

		return $this->render("Default/project-list.twig", [
			"repositories" => $repository->getItemsQuery()->getQuery()->getResult(),
		]);
    }


    /**
     * @Route("/eee", name="index")
     */
    public function index()
    {
//        dd($this->app);
//        $repository = $this->app->entityManager()->getRepository('Entity\Sample');
//        dd($repository->getItemsQuery()->getQuery()->getResult());

        return $this->render('index.twig');
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
			"title" => $title
		]);
	}
}