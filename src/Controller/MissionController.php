<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Mission;
use Symfony\Component\HttpFoundation\Request;
use Ramsey\Uuid\Uuid;

class MissionController extends AbstractController
{
    /**
     * @Route(path="/api/missions", methods={"GET"})
     */
    public function getMissions()
    {
        $missions = $this->getDoctrine()->getRepository(Mission::class)->findAll();
        $serializer = $this->get('serializer');

        $missions = $serializer->normalize($missions);
        return new JsonResponse($missions, 200);
    }

    /**
     * @Route(path="/api/mission/{id}", methods={"GET"})
     */
    public function getMission(Request $request, $id)
    {
        $mission = $this->getDoctrine()->getRepository(Mission::class)->find($id);
        $serializer = $this->get('serializer');

        $mission = $serializer->normalize($mission);
        return new JsonResponse($mission);
    }

    /**
     * @Route(path="/api/mission", methods={"POST"})
     */
    public function createMission()
    {
        $serializer = $this->get('serializer');
        $doctrine = $this->getDoctrine();

        $mission = $serializer->deserialize($request->getContent(), Mission::class, 'json');
        $mission->setId(Uuid::uuid4());

        $doctrine->getManager()->persist($mission);
        $doctrine->getManager()->flush();
    }
}