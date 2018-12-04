<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Armory;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Serializer;

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

        /** @var Serializer $serailizer */
        $serailizer = $this->get('serializer');

        //My Logic
        $armoryContents = $armoryRepository->findAll();

        //Send response
        return new JsonResponse($serailizer->normalize($armoryContents), 200);
    }

    /**
     * @Route(path="/armory", methods={"POST"})
     */
    public function add(Request $request)
    {
        //Dependency
        /** @var Serializer $serailizer */
        $serailizer = $this->get('serializer');
        $doctrine = $this->getDoctrine();

        try {
            //Je Récupère le contenu de message depuis le body
            $armory = $serailizer->deserialize($request->getContent(), Armory::class,  'json');
        }
        //J'attrape tout les erreurs et renvoi une 400 Bad Query
        catch (\Exception $exception) {
            return new JsonResponse([
                'code' => 400,
                'type' => 'Bad Query',
                'message' => 'Mal formatted Query, Body is not a valid JSON'
            ], 400);
        }

        //Je genere un nouvelle ID pour l'objet
        $armory->setId(Uuid::uuid4());

        //Je le sauvegarde dans la DB
        $doctrine->getManager()->persist($armory);
        $doctrine->getManager()->flush();

        //Je renvoi le nouvelle objet crée
        return new JsonResponse($serailizer->normalize($armory), 201);
    }
}
