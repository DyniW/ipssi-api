<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Armory;
use App\Entity\Crs;
use App\Entity\InterventionGroup;
use App\Repository\InterventionGroupRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class InterventionGroupController extends AbstractController
{
    /**
     * @Route(path="/intervention_group", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function list()
    {
        //Dependency
        $InterventionGroupRepository = $this->getDoctrine()->getRepository(InterventionGroup::class);
        $serailizer = $this->get('serializer');

        //Logic
        $interventionGroups = $InterventionGroupRepository->findAllWithFullInterventionGroup();

        //Send response
        return new JsonResponse(
            $serailizer->normalize($interventionGroups, null, ['groups' => 'FullInterventionGroup']),
            200
        );
    }
}
