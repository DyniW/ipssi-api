<?php

declare(strict_types=1);


namespace App\Controller;


use App\Entity\Crs;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class CrsController extends AbstractController
{
    /**
     * @Route(path="/crs", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function list()
    {
        //Dependency
        $crsRepository = $this->getDoctrine()->getRepository(Crs::class);
        $serailizer = $this->get('serializer');

        //My Logic
        $crsContents = $crsRepository->findAll();

        //Send response
        return new JsonResponse($serailizer->normalize($crsContents), 200);
    }
}
