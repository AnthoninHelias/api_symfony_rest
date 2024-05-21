<?php

namespace App\Controller;

use App\Entity\Cartes;
use App\Repository\CartesRepository;
use App\Repository\RareteRepository;
use App\Service\VersioningService;
use Doctrine\ORM\EntityManagerInterface;


use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Request;
use \Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

class CartesController extends AbstractController
{


    #[Route('/api/cartes/{id}', name: 'app_cartes_id', methods: ['GET'])]
    #[OA\Tag(name: 'Cartes')]
    public function getCard(Cartes $carte , SerializerInterface $serializer , VersioningService $versioningService): JsonResponse
    {

        $version = $versioningService->getVersion();
        $context = SerializationContext::create()->setGroups(['cartes']);
        $context->setVersion($version);
        $jsonCard = $serializer->serialize($carte, 'json', $context);



        return new JsonResponse($jsonCard, Response::HTTP_OK, ['accept' => 'json'], true);

    }

    #[Route('/api/cartes/{id}', name: 'deleteCard', methods: ['DELETE'])]
    #[OA\Tag(name: 'Cartes')]
    public function deleteCard(Cartes $carte, EntityManagerInterface $em, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $cachePool->invalidateTags(["cartesCache"]);
        $em->remove($carte);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }


    #[Route('/api/cartes', name:"createCard", methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer une carte')]
    #[OA\Tag(name: 'Cartes')]
    public function createCard(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, RareteRepository $rareteRepository, ValidatorInterface $validator , VersioningService $versioningService): JsonResponse
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


        $context = SerializationContext::create()->setGroups(['cartes']);
        $version = $versioningService->getVersion();
        $context->setVersion($version);

        $jsonCard = $serializer->serialize($carte, 'json', $context);



        $location = $urlGenerator->generate('createCard', ['id' => $carte->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonCard, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/cartes/{id}', name: "updateCard", methods: ['PUT'])]
    #[OA\Tag(name: 'Cartes')]
    public function updateCard(
        Request $request,
        SerializerInterface $serializer,
        Cartes $currentCard,
        EntityManagerInterface $em,
        RareteRepository $rareteRepository,
        VersioningService $versioningService
    ): JsonResponse {
        // Création du contexte de désérialisation
        $deserializationContext = DeserializationContext::create();
        $deserializationContext->setAttribute(AbstractNormalizer::OBJECT_TO_POPULATE, $currentCard);

        // Désérialisation de la carte avec mise à jour de l'objet existant
        $updatedCard = $serializer->deserialize(
            $request->getContent(),
            Cartes::class,
            'json',
            $deserializationContext
        );

        // Mise à jour de la rareté si elle est présente dans le contenu de la requête
        $content = $request->toArray();
        $idRarete = $content['idRarete'] ?? -1;
        $updatedCard->setRarete($rareteRepository->find($idRarete));

        // Persistance de la carte mise à jour
        $em->persist($updatedCard);
        $em->flush();

        // Création du contexte de sérialisation
        $serializationContext = SerializationContext::create()->setGroups(['cartes']);

        // Sérialisation de la carte mise à jour avec le contexte spécifié
        $jsonCard = $serializer->serialize($updatedCard, 'json', $serializationContext);

        // Retour de la réponse JSON
        return new JsonResponse($jsonCard, Response::HTTP_OK, [], true);
    }

    #[Route('/api/cartes', name: 'app_cartes', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Retourne la liste des livres',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Cartes::class, groups: ['cartes'])))
    )]
    #[OA\Parameter(
        name: 'page',
        description: 'La page que l\'on veut récupérer',
        in: 'query',
        schema: new OA\Schema(type: 'int')
    )]
    #[OA\Parameter(
        name: 'limit',
        description: 'Le nombre d\'éléments que l\'on veut récupérer',
        in: 'query',
        schema: new OA\Schema(type: 'int')
    )]
    #[OA\Tag(name: 'Cartes')]
    public function getCardList(CartesRepository $cartesRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cachePool ,  VersioningService $versioningService): JsonResponse
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);


        $idCache = "getCardList-" . $page . "-" . $limit;
        $cartesList = $cachePool->get($idCache, function (ItemInterface $item) use ($cartesRepository, $page, $limit) {
            $item->tag("cartesCache");
            return $cartesRepository->findAllWithPagination($page, $limit);
        });

        $context = SerializationContext::create()->setGroups(['cartes']);
        $version = $versioningService->getVersion();
        $context->setVersion($version);
        $jsonCardList = $serializer->serialize($cartesList, 'json', $context);


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


//    #[Route('/api/cartes', name: 'app_cartes', methods: ['GET'])]
//    public function getCardList(CartesRepository $cartesRepository, SerializerInterface $serializer): JsonResponse
//    {
//        $cartesList= $cartesRepository->findAll();
//        $jsonCardList = $serializer->serialize($cartesList, 'json');
//        return new JsonResponse($jsonCardList, Response::HTTP_OK, [], true);
//
//    }