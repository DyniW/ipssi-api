<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * @Route(path="/", methods={"GET"})
     */
    public function home()
    {
        $string = "<html><body><script src='//unpkg.com/swagger-ui-dist@3/swagger-ui-bundle.js'></script></body></html>";
        return new Response($string);
    }

}