<?php

namespace App\Controller;

use App\Entity\Rarete;
use App\Repository\RareteRepository;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use \Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

class RareteController extends AbstractController
{
    #[Route('/api/rarete', name: 'app_rarete', methods: ['GET'])]
    public function getRareteList(RareteRepository $rareteRepository, SerializerInterface $serializer, TagAwareCacheInterface $cachePool , Request $request): JsonResponse
    {

        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);


        $idCache = "getCardList-" . $page . "-" . $limit;
        $rareteList = $cachePool->get($idCache, function (ItemInterface $item) use ($rareteRepository, $page, $limit) {
            $item->tag("cartesCache");
            return $rareteRepository->findAllWithPagination($page, $limit);
        });

        $context = SerializationContext::create()->setGroups(['rarete']);
        $jsonRareteList = $serializer->serialize($rareteList, 'json', $context);

        return new JsonResponse($jsonRareteList, Response::HTTP_OK, [], true);

    }


    #[Route('/api/rarete/{id}', name: 'app_rarete_id', methods: ['GET'])]
    public function getRarete(Rarete $rarete , SerializerInterface $serializer): JsonResponse
    {
        $jsonRarete= $serializer->serialize($rarete, 'json');
        return new JsonResponse($jsonRarete, Response::HTTP_OK, ['accept' => 'json'], true);

    }



    #[Route('/api/rarete/{id}', name: 'deleteRarete', methods: ['DELETE'])]
    public function deleteRarete(Rarete $rarete , EntityManagerInterface $em, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $cachePool->invalidateTags(["rareteCache"]);
        $em->remove($rarete);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }


    #[Route('/api/rarete', name:"createRarete", methods: ['POST'])]
    public function createRarete(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {

        $rarete = $serializer->deserialize($request->getContent(), Rarete::class, 'json');

        $errors = $validator->validate($rarete);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), Response::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($rarete);
        $em->flush();


        $context = SerializationContext::create()->setGroups(['rarete']);
        $jsonRarete = $serializer->serialize($rarete, 'json', $context);

        $location = $urlGenerator->generate('createRarete', ['id' => $rarete->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonRarete, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/rarete/{id}', name: 'updateRarete', methods: ['PUT'])]
    public function updateRarete(
        Request $request,
        SerializerInterface $serializer,
        Rarete $currentRarete,
        EntityManagerInterface $em
    ): JsonResponse {
        // Création du contexte de désérialisation
        $deserializationContext = DeserializationContext::create();
        $deserializationContext->setAttribute(AbstractNormalizer::OBJECT_TO_POPULATE, $currentRarete);

        // Désérialisation de la rareté avec mise à jour de l'objet existant
        $updatedRarete = $serializer->deserialize(
            $request->getContent(),
            Rarete::class,
            'json',
            $deserializationContext
        );

        // Persistance de la rareté mise à jour
        $em->persist($updatedRarete);
        $em->flush();

        // Retour d'une réponse JSON vide avec un code HTTP 204 (No Content)
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }


}
