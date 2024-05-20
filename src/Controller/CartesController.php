<?php

namespace App\Controller;

use App\Entity\Cartes;
use App\Repository\CartesRepository;
use App\Repository\RareteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class CartesController extends AbstractController
{
//    #[Route('/api/cartes', name: 'app_cartes', methods: ['GET'])]
//    public function getCardList(CartesRepository $cartesRepository, SerializerInterface $serializer): JsonResponse
//    {
//        $cartesList= $cartesRepository->findAll();
//        $jsonCardList = $serializer->serialize($cartesList, 'json');
//        return new JsonResponse($jsonCardList, Response::HTTP_OK, [], true);
//
//    }

    #[Route('/api/cartes/{id}', name: 'app_cartes_id', methods: ['GET'])]
    public function getCard(Cartes $carte , SerializerInterface $serializer): JsonResponse
    {
        $jsonCard= $serializer->serialize($carte, 'json');
        return new JsonResponse($jsonCard, Response::HTTP_OK, ['accept' => 'json'], true);

    }

    #[Route('/api/cartes/{id}', name: 'deleteCard', methods: ['DELETE'])]
    public function deleteCard(Cartes $carte, EntityManagerInterface $em, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $cachePool->invalidateTags(["cartesCache"]);
        $em->remove($carte);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }


    #[Route('/api/cartes', name:"createCard", methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer une carte')]
    public function createCard(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, RareteRepository $rareteRepository, ValidatorInterface $validator): JsonResponse
    {

        $carte = $serializer->deserialize($request->getContent(), Cartes::class, 'json');

        $errors = $validator->validate($carte);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }
        // Récupération de l'ensemble des données envoyées sous forme de tableau
        $content = $request->toArray();

        // Récupération de l'idRarete. S'il n'est pas défini, alors on met -1 par défaut.
        $idRarete = $content['idRarete'] ?? -1;

        // On cherche la rareté qui correspond et on l'assigne au livre.
        // Si "find" ne trouve pas la rareté, alors null sera retourné.
        $carte->setRarete($rareteRepository->find($idRarete));



        $em->persist($carte);
        $em->flush();

        $jsonBook = $serializer->serialize($carte, 'json', ['groups' => 'cartes']);

        $location = $urlGenerator->generate('createCard', ['id' => $carte->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonBook, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/cartes/{id}', name:"updateCard", methods:['PUT'])]

    public function updateCard(Request $request, SerializerInterface $serializer, Cartes $currentCard, EntityManagerInterface $em, RareteRepository $rareteRepository): JsonResponse
    {
        $updatedCard = $serializer->deserialize($request->getContent(),
            Cartes::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentCard]);

        $content = $request->toArray();
        $idRarete = $content['idRarete'] ?? -1;
        $updatedCard->setRarete($rareteRepository->find($idRarete));

        $em->persist($updatedCard);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/cartes', name: 'app_cartes', methods: ['GET'])]
    public function getCardList(CartesRepository $cartesRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);


        $idCache = "getCardList-" . $page . "-" . $limit;
        $cartesList = $cachePool->get($idCache, function (ItemInterface $item) use ($cartesRepository, $page, $limit) {
            $item->tag("cartesCache");
            return $cartesRepository->findAllWithPagination($page, $limit);
        });

        $jsonCardList = $serializer->serialize($cartesList, 'json');
        return new JsonResponse($jsonCardList, Response::HTTP_OK, [], true);
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