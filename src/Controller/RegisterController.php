<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Film;
use App\Entity\User;
use App\Repository\FilmRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class RegisterController extends AbstractController
{
    /**
     * @Route("/register", name="register")
     */
    public function index(): Response
    {
        return $this->render('register/index.html.twig', [
            'controller_name' => 'RegisterController',
        ]);
    }

    private $passwordEncoder;
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route(name="api_login", path="/api/login_check")
     * @return JsonResponse
     */
    public function api_login(): JsonResponse
    {
        $user = $this->getUser();

        return new JsonResponse([
            'username' => $user->getUsername(),
            'roles' => $user->getRoles(),
        ]);
    }

    /**
     *
     * @Route("/api/login", name="login", methods={"POST"})
     *
     * @param AuthenticationUtils $authenticationUtils
     *
     * @return JsonResponse
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return new JsonResponse([$lastUsername, $error]);
    }

    /**
     * @Route("api/logout", name="logout", methods={"POST"})
     */
    public function logout()
    {

    }


    /**
     * @Route("/register/user", name="userCreate", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function userCreate(Request $request, UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $em = $this->getDoctrine()->getManager();

        $data = json_decode($request->getContent(), true);
        $user = new User();
        $password = $userPasswordEncoder->encodePassword($user , $data['password']);
        $user
            ->setUsername($data['username'])
            ->setPassword($password);
        $em->persist($user);
        $em->flush();
        return JsonResponse::fromJsonString($this->serializeJson($user));
    }

    /**
     * @Route("/api/register/update", name="userUpdate", methods={"PUT"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @return JsonResponse
     */
    public function userUpdate(Request $request, UserRepository $userRepository)
    {
        $em = $this->getDoctrine()->getManager();
        $data = json_decode($request->getContent(),true);
        $user = $userRepository->findOneBy(['id' => $data['id']]);

        if($data["mail"]){
            $user->setMail($data['mail']);
        }
        if($data["password"]){
            $user->setPassword($this->passwordEncoder->encodePassword($user, $data['password']));
        }
        $em->persist($user);
        $em->flush();
        return JsonResponse::fromJsonString($this->serializeJson($user));
    }

    /**
     * @Route("/api/register/delete", name="userDelete", methods={"DELETE"})
     * @param Request $request
     * @param UserRepository $userRepository
     * @return JsonResponse
     */
    public function userDelete(Request $request, UserRepository $userRepository)
    {
        $em = $this->getDoctrine()->getManager();
        $data = json_decode($request->getContent(),true);
        $user = $userRepository->findOneBy(['id' => $data['id']]);

        $em->remove($user);
        $em->flush();
        return JsonResponse::fromJsonString($this->serializeJson($user));
    }

    private function serializeJson($objet)
    {
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object->getSlug();
            },
        ];
        $normalizer = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);
        $serializer = new Serializer([$normalizer], [new JsonEncoder()]);

        return $serializer->serialize($objet, 'json');
    }
}
