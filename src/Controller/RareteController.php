<?php

namespace App\Controller;

use App\Entity\Rarete;
use App\Repository\RareteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use \Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class RareteController extends AbstractController
{
    #[Route('/api/rarete', name: 'app_rarete', methods: ['GET'])]
    public function getRareteList(RareteRepository $rareteRepository, SerializerInterface $serializer): JsonResponse
    {
        $rareteList= $rareteRepository->findAll();
        $jsonRareteList = $serializer->serialize($rareteList, 'json',['groups' => 'rarete']);
        return new JsonResponse($jsonRareteList, Response::HTTP_OK, [], true);

    }


    #[Route('/api/rarete/{id}', name: 'app_rarete_id', methods: ['GET'])]
    public function getRarete(Rarete $rarete , SerializerInterface $serializer): JsonResponse
    {
        $jsonRarete= $serializer->serialize($rarete, 'json');
        return new JsonResponse($jsonRarete, Response::HTTP_OK, ['accept' => 'json'], true);

    }

}
