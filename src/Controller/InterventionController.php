<?php

declare(strict_types=1);


namespace App\Controller;

use App\Entity\Crs;
use App\Entity\InterventionGroup;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class InterventionController extends AbstractController
{
    /**
     * @Route(path="/intervention", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function list()
    {
        //Dependency
        $interventionRepository = $this->getDoctrine()->getRepository(InterventionGroup::class);
        $serailizer = $this->get('serializer');

        $interventionGroups = $interventionRepository->findAll();

        $interventionGroups = $serailizer->normalize($interventionGroups);

        $res = [];

        foreach ($interventionGroups as $groups){
            $Crs = $this->getDoctrine()->getRepository(Crs::class)->findBy(
                ['group' => $groups['id']]
            );

            $groups['crs'] = $Crs;
            $res[] = $groups;
        }

        //Send response
        return new JsonResponse($serailizer->normalize($res), 200);
    }
}
