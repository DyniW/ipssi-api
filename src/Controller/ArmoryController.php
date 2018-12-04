<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Armory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Ramsey\Uuid\Uuid;

class ArmoryController extends AbstractController 
{
    /**
     * @Route(path="/armory", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function list()
    {
        //Dependency
        $armoryRepository = $this->getDoctrine()->getRepository(Armory::class);
        $serailizer = $this->get('serializer');

        //My Logic
        $armoryContents = $armoryRepository->findAll();

        //Send response
        return new JsonResponse($serailizer->normalize($armoryContents), 200);
    }

    /**
     * @Route(path="/api/armory", methods={"POST"}) 
    */
    public function add(Request $request)
    {
        $serializer = $this->get('serializer');
        $doctrine = $this->getDoctrine();

        $armory = $serializer->deserialize($request->getContent(), Armory::class, 'json');
        $armory->setId(Uuid::uuid4());

        $doctrine->getManager()->persist($armory);
        $doctrine->getManager()->flush();

        return new JsonResponse($serializer->normalize($armory), 201);
    }
}
