<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ProfilRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
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

    #[Route('/profil/user', name: 'profil', methods: ['GET'])]
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
    #[Route('/profil/user/update', name: 'profil.update', methods: ['POST'])]
    public function updateProfilData(Request $request): Response {

        $actualUser = $this->TokenAuth($request);
        if (!$actualUser) {
            return $this->json([
                "status" => "error",
                "message" => "Il n'y a pas d'utilisateur avec ce token",
            ]);
        }

        $actualProfil = $this->profilRepo->findOneBy(['user_id' => $actualUser]);

        // C'est exactement ce ue ta fait mais j'ai remplacer data par bio et apr file car ca devien 2 choses diff avec l'upload de fichier


        // bio
        $bio = $request->request->get('bio');
        if (!empty($bio)) {
            $actualProfil->setBio($bio);
        }

        // imageProfil pour faire l'upload
        $file = $request->files->get('imageProfil');
        if ($file) {
            $filesystem = new Filesystem();
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/profiles'; // c'est dans ce fichier ue les pp seront stocker

            // supprime l'ancien fichier image
            $oldFilename = $actualProfil->getImageProfil();
            if ($oldFilename && $filesystem->exists($uploadDir . '/' . $oldFilename)) {
                $filesystem->remove($uploadDir . '/' . $oldFilename);
            }

            // génération nom et placement dans fichier public
            $newFilename = uniqid() . '.' . $file->guessExtension();
            try {
                $file->move($uploadDir, $newFilename);
                $actualProfil->setImageProfil($newFilename);
            } catch (FileException $e) {
                return $this->json(["status" => "error", "message" => "Erreur upload"]);
            }
        }

        $this->em->persist($actualProfil);
        $this->em->flush();

        return $this->json(["status" => "ok",
                            "message" => "Update profil réussie",
                            "result" => $actualProfil,], 200, [], ['groups' => ['profil:read']
        ]);
    }
}
