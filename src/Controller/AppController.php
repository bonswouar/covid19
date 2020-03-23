<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    /**
     * @Route("/", name="app_index")
     */
    public function index()
    {
        $maxCountries = $this->getParameter('app.graph_max_countries');;
        return $this->render('index.html.twig', [
            'maxCountries' => $maxCountries
        ]);
    }
}
