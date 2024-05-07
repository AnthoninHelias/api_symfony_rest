<?php

namespace App\Controller;

use App\Entity\Rarete;
use App\Repository\RareteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use \Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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



    #[Route('/api/rarete/{id}', name: 'deleteRarete', methods: ['DELETE'])]
    public function deleteRarete(Rarete $rarete , EntityManagerInterface $em): JsonResponse
    {
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

        $jsonBook = $serializer->serialize($rarete, 'json', ['groups' => 'rarete']);

        $location = $urlGenerator->generate('createRarete', ['id' => $rarete->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonBook, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/rarete/{id}', name:"updateRarete", methods:['PUT'])]

    public function updateCard(Request $request, SerializerInterface $serializer, Rarete $currentRarete, EntityManagerInterface $em): JsonResponse
    {
        $updatedRarete = $serializer->deserialize($request->getContent(),
            Rarete::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentRarete]);

        $em->persist($updatedRarete);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT );
    }

}
