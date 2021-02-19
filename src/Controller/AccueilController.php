<?php

namespace App\Controller;

use App\Entity\Film;
use App\Repository\FilmRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class AccueilController extends AbstractController
{
    protected function serializeJson($objet){
        $encoder = new JsonEncoder();
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getNom();
            },
        ];
        $normalizer = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);
        $serializer = new Serializer([$normalizer], [$encoder]);
        $jsonContent = $serializer->serialize($objet, 'json');
        return $jsonContent;
    }



    /**
     * @Route("/accueil", name="accueil")
     */
    public function index(): Response
    {
        return $this->render('accueil/index.html.twig', [
            'controller_name' => 'AccueilController',
        ]);
    }

    /**
     * @Route("/api/create", name="create", methods="POST")
     * @param Request $request
     * @param FilmRepository $filmRepository
     */
    public function create(Request $request, FilmRepository $filmRepository) : Response
    {
        $date = new \DateTime('now');
        $entityManager = $this->getDoctrine()->getManager();
        $data = json_decode($request->getContent(),true);
        $film = new Film();
        $film->setNom($data['name'])
            ->setSynopsis($data['synopsis'])
            ->setType($data['type'])
            ->setCreatedAt($date);

        $entityManager->persist($film);
        $entityManager->flush();
        return JsonResponse::fromJsonString($this->serializeJson($film), Response::HTTP_OK);
    }

//getallfilm et getfilm by id

    /**
     * @Route("/api/film", name="film")
     * @param FilmRepository $filmRepository
     * @param Request $request
     * @return JsonResponse
     */
    public function film(FilmRepository $filmRepository, Request $request)
    {
        $filter = [];
        $em = $this->getDoctrine()->getManager();
        $metadata = $em->getClassMetadata(Film::class)->getFieldNames();
        foreach ($metadata as $value){
            if ($request->query->get($value)){
                $filter[$value] = $request->query->get($value);
            }
        }
        return JsonResponse::fromJsonString($this->serializeJson($filmRepository->findBy($filter)), Response::HTTP_OK);
    }




}
