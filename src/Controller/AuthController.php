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


use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

final class AuthController extends AbstractController
{

    public function __construct(private UserRepository $userRepo){}

    private function getSalt(): string
    {
        return md5($this->getParameter('app.password_salt'));
    }

    #[Route('/auth/register', name: 'auth_register', methods: ['POST'])]
    public function register(Request $request, EntityManagerInterface $em, ValidatorInterface $validator, JWTTokenManagerInterface $jwtManager): Response
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
        $salt = $this->getSalt();

        $user->setPseudo($data['pseudo'] ?? '');
        $user->setEmail($data['email'] ?? '');

        //PASSWORD
        $rawPassword = $data['password'] ?? '';
        $hashedPassword = md5($rawPassword . $salt);
        $user->setPassword($hashedPassword);

        $user->setRole([]);
        $user->setCreatedAt(new \DateTimeImmutable());

        $profil->setImageProfil("");
        $profil->setBio("Bienvenue sur AnimVerse !!");
        $profil->setUserId($user);

        //Validator
        $userErrors = $validator->validate($user);
        $profilErrors = $validator->validate($profil);

        if (count($userErrors) > 0 || count($profilErrors) > 0) {
            return $this->json([
                "status" => "error",
                "message" => "Erreur de validation",
                "errors" => (string) $userErrors . (string) $profilErrors
            ], 400);
        }

        $em->persist($user);
        $em->persist($profil);
        $em->flush();

        $token = $jwtManager->create($user);

        return $this->json([
            "status" => "ok",
            "message" => "Compte créé avec succès",
            "token" => $token,
            "result" => $user
            ] , 201, [], ['groups' => ['user:read_id', 'user:read_pseudo', 'user:read_email', 'user:read_date', 'user:read_role']]);
    }

    #[Route('/auth/login', name: 'auth_login', methods: ['POST'])]
    public function login(Request $request, JWTTokenManagerInterface $jwtManager): Response{

        $data = json_decode($request->getContent(), true);

        if(!$data){
            return $this->json(["status" => "error", "message" => "Donnée JSON invalid"],400);
        }

        $account = $this->userRepo->findOneBy(['email' => $data['email']?? '']);
        if(!$account){
            return $this->json(["status"=> "error", "message"=>"Email incorrect"],401);
        }

        $salt = $this->getSalt();

        if(md5(($data['password']?? '').$salt) === $account->getPassword()){
            $token = $jwtManager->create($account);

            return $this->json([
                "status" => "ok",
                "message" => "Connecté",
                "token" => $token,
                "result" => $account,
                ], 200, [], ['groups' => ['user:read_id', 'user:read_pseudo', 'user:read_email', 'user:read_role']]);
            }

            else {
                return $this->json(["status"=> "error", "message"=>"Mot de passe incorrect"], 401);
            }

    }

    #[Route('/auth/token', name: 'auth_token', methods: ['GET'])]
    public function token(JWTTokenManagerInterface $jwtManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(["status" => "error", "message" => "Non authentifié"], 401);
        }

        $token = $jwtManager->create($user);

        return $this->json([
            "status" => "ok",
            "message" => "Token valide",
            "token" => $token,
            "result" => $user,
        ], 200, [], ['groups' => ['auth:token']]);
    }
}
