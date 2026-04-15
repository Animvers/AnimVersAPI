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


final class AuthController extends AbstractController
{

    public function __construct(private UserRepository $userRepo){}

    private function getSalt(): string{
        return md5($this->getParameter('app.password_salt'));
    }


    #[Route('/auth/login', name: 'auth_login', methods: ['POST', 'OPTIONS'])]
    public function login(Request $request): Response{

        $data = json_decode($request->getContent(), true);

        if(!$data){
            return $this->json(["status" => "error", "message" => "Donnée JSON invalid"]);
        }

        $account = $this->userRepo->findOneBy(['email' => $data['email']]);
        if(!$account){
            return $this->json(["status"=> "error", "message"=>"Email incorrect"]);
        }

        $salt = $this->getSalt();

        if(md5($data['password'].$salt) === $account->getPassword()){
            return $this->json([
                "status" => "ok",
                "message" => "Connecté",
                "result" => $account,
                ], 200, [], ['groups'=>['user:read']]
            );
        }else {
            return $this->json(["status"=> "error", "message"=>"Mot de passe incorrect"]);
        }
    }

    #[Route('/auth/register', name: 'auth_register', methods: ['POST', 'OPTIONS'])]
    public function register(Request $request, EntityManagerInterface $em): Response{
        $data = json_decode($request->getContent(), true);
        if(!$data){
            return $this->json([
               "status" => "error",
               "message" => "Donner JSON invalid"
            ]);
        }


        if($this->userRepo->findOneBy(['email' => $data['email']])){
            return $this->json(["status"=> "error", "message"=>"Email déjà utilisé"]);
        }
        if($this->userRepo->findOneBy(['pseudo' => $data['pseudo']])){
            return $this->json(["status" => "error", "message"=>"Pseudonyme existant Désolée fallait être la avant ^^ "]);
        }

        $user = new User();
        $profil = new Profil();
        $salt = $this->getSalt();


        $user->setPseudo($data['pseudo']);
        $user->setEmail($data['email']);

        //PASSWORD
        $hashedPassword = md5($data['password'].$salt);
        $user->setPassword($hashedPassword);

        //TOKEN
        $hashedPseudo = md5($data['pseudo']);
        $tokenRaw = $hashedPseudo.uniqid('token', true);
        $user->setToken(hash('sha256', $tokenRaw));

        $user->setRole(['ROLE_USER']);
        $user->setCreatedAt(new \DateTimeImmutable());

        $profil->setImageProfil("");
        $profil->setBio("Bienvenue sur AnimVerse !!");
        $profil->setUserId($user);

        $em->persist($user);
        $em->persist($profil);

        $em->flush();

        return $this->json([
           "status" => "ok",
           "message" => "compte crée",
            "result" => $user, 200, [], ['groups'=>['user:read']]
        ]);
    }

    #[Route('/auth/token', name: 'auth_token_login', methods: ['GET', 'OPTIONS'])]
    public function logout(Request $request): Response{

        $token = $request->headers->get('Authorization');

        if(!$token){
            return $this->json(["status"=> "error", "message"=> "token vide"]);
        }


        $token = substr($token, 7);
        $user = $this->userRepo->findOneBy(['token' => $token]);


        if(!$user){
            return $this->json(["status"=> "error", "message"=> "token vide"]);
        }

        return $this->json(["status"=> "ok", "message"=>"Token valid", "result" => $user], 200, [], ['groups'=>['user:read']]);
    }
}
