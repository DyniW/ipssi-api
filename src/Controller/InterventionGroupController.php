<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\InterventionGroup;
use App\Entity\Crs;

class InterventionGroupController extends AbstractController
{
    /**
     * @Route(path="/api/intervention-group", name="intervention-group")
     */
    public function getInterventionGroups()
    {
        $interventionGroup = $this->getDoctrine()->getRepository(InterventionGroup::class)->findAll();

        $serializer = $this->get('serializer');
        $interventionGroup = $serializer->normalize($interventionGroup);

        foreach($interventionGroup as $group){
            $crsList = $this->getDoctrine()->getRepository(Crs::class)->findBy(
                [ 'group' => $group['id'] ]
            );
            $group['crs'] = $serializer->normalize($crsList);
            $res[] = $group;
        }
        return new JsonResponse($res, 200);
    }
}