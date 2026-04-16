<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ProfilRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfilController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepo,
        private ProfilRepository $profilRepo
    )
    {
    }

    public function TokenAuth(Request $request): ?User{
        $tokenHeader = $request->headers->get('Authorization');
        if(!$tokenHeader){return null;}

        $token = str_replace('Bearer ', '', $tokenHeader);
        return $this->userRepo->findOneBy(['token' => $token]);
    }

   #[Route('/profil/user', name: 'profil', methods: ['GET', 'OPTIONS'])]
    public function getProfilData(Request $request): Response{

        $actualUser = $this->TokenAuth($request);

        if(!$actualUser){
            return $this->json([
                "status" => "error",
                "message" => "Il n'y a pas d'utilisateur avec ce token",
            ]);
        }

        $actualProfil = $this->profilRepo->findOneBy(['user_id' => $actualUser]);

        return $this->json([
            "status" => "ok",
            "message" => "profil de l'utilisateur",
            "result" => $actualProfil, $actualUser], 200, [], ['groups' => ['profil:read', 'user:read']
        ]);
   }

   #[Route('/profil/user/update', name: 'profil.update', methods: ['PUT', 'OPTIONS'])]
    public function updateProfilData(Request $request ): Response{

        $actualUser = $this->TokenAuth($request);
       if(!$actualUser){
           return $this->json([
               "status" => "error",
               "message" => "Il n'y a pas d'utilisateur avec ce token",
           ]);
       }

       $actualProfil = $this->profilRepo->findOneBy(['user_id' => $actualUser]);

       $data = json_decode($request->getContent(), true);
       if(!$data){
           return $this->json(["status" => "error",
                                "message" => "Le formulaire ne passe pas"
           ]);
       }

       if(!empty($data['bio'])) $actualProfil->setBio($data['bio']);
       if(!empty($data['imageProfile'])) $actualProfil->setImageProfil($data['imageProfile']);

       $this->em->persist($actualProfil);
       $this->em->flush();

       return $this->json(["status" => "ok",
                            "message" => "Update profil réussie",
                            "result" => $actualProfil,], 200, [], ['groups' => ['profil:read']
        ]);
   }
}
