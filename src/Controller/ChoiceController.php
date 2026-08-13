<?php

namespace App\Controller;

use App\Entity\Reponses;
use App\Entity\User;
use App\Repository\ChoiceRepository;
use App\Repository\SondageRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ChoiceController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepo,
        private SondageRepository $sondageRepo,
        private ChoiceRepository $choiceRepo,
        private EntityManagerInterface $em
    )
    {}

    public function TokenAuth(Request $request): ?User{
        $tokenHeader = $request->headers->get('Authorization');
        if(!$tokenHeader){return null;}

        $token = str_replace('Bearer ', '', $tokenHeader);
        return $this->userRepo->findOneBy(['token' => $token]);
    }


    //Verification du sondage et de la data | voir si la data est good / voir si le sondage et le choix existe / voir si le choix est bien dans ce sondage
    public function pollVerif(Request $request): ?Response{
        $data = json_decode($request->getContent(), true);
        if(!$data){
            return $this->json(["status"=> "error", "message"=> "JSON vide ou valeur incorrect"]);
        }
        if(!isset($data["sondage_id"]) || !isset($data["choice_id"]) ){
            return $this->json(["status"=> "error", "message"=> "sondage_id ou choix_id incorrect"]);
        }

        $sondage = $this->sondageRepo->find($data["sondage_id"]);
        $choice = $this->choiceRepo->find($data["choice_id"]);

        if(!$sondage){
            return $this->json(["status"=> "error", "message"=> "sondage inexistant"]);
        }
        if(!$choice){
            return $this->json(["status"=> "error", "message"=> "choice inexistant"]);
        }

        if($choice->getWhichPoll() !== $sondage){
            return $this->json(["status"=> "error", "message"=> "choice inexistant dans ce sondage"]);
        }

        return null;

    }

    #[Route('/choice/selected', name: 'choice_selected', methods: ['POST'])]
    public function ChoiceSelected(Request $request): Response{

        $actualUser = $this->tokenAuth($request);
        if(!$actualUser){
            return $this->json(["status"=> "error", "message"=> "Utilisateur inexistant"]);
        }

        $verif = $this->pollVerif($request);
        if($verif){
            return $verif;
        }

        $data = $request->getContent();
        $choice = $this->choiceRepo->find($data["choice_id"]);

        $reponse = new Reponses();
        $reponse->setUserId($actualUser);
        $reponse->setChoiceId($choice);

        $this->em->persist($reponse);
        $this->em->flush();

        return $this->json(["status"=>"success", "message"=>"Choix valider "/*, "result"=> $reponse*/]); // a vérif mais risque de boucle dans la DB

    }

}
