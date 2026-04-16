<?php

namespace App\Controller;

use App\Entity\Sondage;
use App\Entity\User;
use App\Repository\SondageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SondageController extends AbstractController
{

    public function __construct(
        private UserRepository $userRepo,
        private SondageRepository $sondageRepo,
        private EntityManagerInterface $em
    )
    {}

    public function TokenAuth(Request $request): ?User{
        $tokenHeader = $request->headers->get('Authorization');
        if(!$tokenHeader){return null;}

        $token = str_replace('Bearer ', '', $tokenHeader);
        return $this->userRepo->findOneBy(['token' => $token]);
    }

    //METHODS GET

    #[Route('/sondage/all', name: 'get_All', methods: ['GET', 'OPTIONS'])]
    public function getAll(): Response{

        $sondages = $this->sondageRepo->findAll();

        if(!$sondages){
            return $this->json(["status"=>"error", "message"=>"Pas de sondages disponible"]);
        }

        return $this->json(["status"=>"ok",
                            "message" => "Sondages bien présent",
                            "result"=>$sondages], 200, [], ['groups' => ['sondage:read']]
        );
    }


    #[Route('/sondage/create', name: 'sondage_create', methods: ['POST', 'OPTIONS'])]
    public function sondageCreate(Request $request, EntityManagerInterface $em): Response{

        $actualUser = $this->TokenAuth($request);
        $data = json_decode($request->getContent(), true);

        if(!$data){
            return $this->json(["status"=> "error", "message"=> "JSON vide ou valeur incorrect"]);
        }
        if(!$actualUser){
            return $this->json(["status"=> "error", "message"=>"Utilisateur inexistant"]);
        }
        if($actualUser->getRole() != ["ROLE_ADMIN"]){
            return $this->json(["status"=>"error", "message" => "autorisations refusée"]);
        }

        $newSondage = new Sondage();

        $newSondage->setImageUrl($data['imageUrl']);
        $newSondage->setTitle($data['title']);
        $newSondage->setQuestion($data['question']);
        $newSondage->setCategoryName($data['category_name']);

        $newSondage->setIsActive(true);

        $newSondage->setWhoMakeIt($actualUser);

        $newSondage->setCreateAt(new \DateTimeImmutable());

        $em->persist($newSondage);
        $em->flush();

        return $this->json([
            "status" => "ok",
            "message" => "Card Crée avec success",
            "result"=> $newSondage], 200, [], ['groups' => ['sondage:read']
        ]);
    }



}
