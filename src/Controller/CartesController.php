<?php

namespace App\Controller;

use App\Entity\Cartes;
use App\Repository\CartesRepository;
use \Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

class CartesController extends AbstractController
{
    #[Route('/api/cartes', name: 'app_cartes', methods: ['GET'])]
    public function getCardList(CartesRepository $cartesRepository, SerializerInterface $serializer): JsonResponse
    {
        $cartesList= $cartesRepository->findAll();
        $jsonCardList = $serializer->serialize($cartesList, 'json');
        return new JsonResponse($jsonCardList, Response::HTTP_OK, [], true);

    }

    #[Route('/api/cartes/{id}', name: 'app_cartes_id', methods: ['GET'])]
    public function getCard(Cartes $carte , SerializerInterface $serializer): JsonResponse
    {
        $jsonCard= $serializer->serialize($carte, 'json');
        return new JsonResponse($jsonCard, Response::HTTP_OK, ['accept' => 'json'], true);

    }
}















/*
  #[Route('/api/cartes/{id}', name: 'app_cartes', methods: ['GET'])]
  public function getCard(int $id, CartesRepository $cartesRepository, SerializerInterface $serializer): JsonResponse
  {
      $carte = $cartesRepository->find($id);
      if($carte)
      {
          $jsonCard = $serializer->serialize($carte, 'json');
          return new JsonResponse($jsonCard, 200, [], true);
      }
      return new JsonResponse('Card not found', 404);
  }
      */