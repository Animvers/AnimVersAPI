<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Profil;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final class AuthController extends AbstractController
{

    public function __construct(private UserRepository $userRepo){}

    #[Route('/auth/register', name: 'auth_register', methods: ['POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        JWTTokenManagerInterface $jwtManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $data = json_decode($request->getContent(), true);

        if(!$data)
        {
            return $this->json([
                "status" => "error",
                "message" => "Donner JSON invalid"
            ],400);
        }


        if (!empty($data['email']) && $this->userRepo->findOneBy(['email' => $data['email']])) {
            return $this->json(["status" => "error", "message" => "Email déjà utilisé"], 400);
        }
        if (!empty($data['pseudo']) && $this->userRepo->findOneBy(['pseudo' => $data['pseudo']])) {
            return $this->json(["status" => "error", "message" => "Pseudonyme existant Désolée fallait être la avant ^^ "], 400);
        }

        $user = new User();
        $profil = new Profil();

        $user->setPseudo($data['pseudo'] ?? '');
        $user->setEmail($data['email'] ?? '');

        //PASSWORD (Définir le mot de passe brut avant validation)
        $rawPassword = $data['password'] ?? '';
        $user->setPassword($rawPassword);

        $user->setRole([]);
        $user->setCreatedAt(new \DateTimeImmutable());

        $profil->setImageProfil("");
        $profil->setBio("Bienvenue sur AnimVerse !!");
        $profil->setUserId($user);

        //Validator (Vérification de la longueur du mot de passe brut)
        $userErrors = $validator->validate($user);
        $profilErrors = $validator->validate($profil);

        if (count($userErrors) > 0 || count($profilErrors) > 0) {
            return $this->json([
                "status" => "error",
                "message" => "Erreur de validation",
                "errors" => (string) $userErrors . (string) $profilErrors
            ], 400);
        }

        // Hacher le mot de passe après validation avec le PasswordHasher de Symfony
        $hashedPassword = $passwordHasher->hashPassword($user, $rawPassword);
        $user->setPassword($hashedPassword);

        $em->persist($user);
        $em->persist($profil);
        $em->flush();

        $token = $jwtManager->create($user);

        return $this->json([
            "status" => "ok",
            "message" => "Compte créé avec succès",
            "token" => $token,
            "result" => $user
            ] , 201, [], ['groups' => ['user:read']]);
    }

    #[Route('/auth/login', name: 'auth_login', methods: ['POST'])]
    public function login(
        Request $request,
        JWTTokenManagerInterface $jwtManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response{

        $data = json_decode($request->getContent(), true);

        if(!$data){
            return $this->json(["status" => "error", "message" => "Donnée JSON invalid"],400);
        }

        $account = $this->userRepo->findOneBy(['email' => $data['email']?? '']);
        if(!$account){
            return $this->json(["status"=> "error", "message"=>"Email incorrect"],401);
        }

        if($passwordHasher->isPasswordValid($account, $data['password'] ?? '')){
            $token = $jwtManager->create($account);

            return $this->json([
                "status" => "ok",
                "message" => "Connecté",
                "token" => $token,
                "result" => $account,
                ], 200, [], ['groups' => ['user:read']]);
            }

            else {
                return $this->json(["status"=> "error", "message"=>"Mot de passe incorrect"], 401);
            }

    }

    #[Route('/auth/token', name: 'auth_token', methods: ['GET'])]
    public function token(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(["status" => "error", "message" => "Non authentifié"], 401);
        }

        return $this->json([
            "status" => "ok",
            "message" => "Token valide",
            "result" => $user,
        ], 200, [], ['groups' => ['auth:token']]);
    }
}
