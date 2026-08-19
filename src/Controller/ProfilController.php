<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ProfilRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ProfilController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $em,
        private ProfilRepository $profilRepo
    ) {}

    #[Route('/profil/user', name: 'profil', methods: ['GET'])]
    public function getProfilData(): Response
    {
        $actualUser = $this->getUser();

        if (!$actualUser) {
            return $this->json([
                "status" => "error",
                "message" => "Utilisateur non authentifié",
            ], 401);
        }

        $actualProfil = $this->profilRepo->findOneBy(['user_id' => $actualUser]);

        return $this->json([
            "status" => "ok",
            "message" => "Profil de l'utilisateur",
            "result" => [
                "user" => $actualUser,
                "profil" => $actualProfil
            ]
        ], 200, [], ['groups' => ['profil:read', 'user:read_id', 'user:read_pseudo', 'user:read_email']]);
    }


    #[Route('/profil/user/update', name: 'profil.update', methods: ['POST'])]
    public function updateProfilData(Request $request, ValidatorInterface $validator): Response {

        $actualUser = $this->getUser();

        if (!$actualUser) {
            return $this->json([
                "status" => "error",
                "message" => "Utilisateur non authentifié",
            ], 401);
        }

        $actualProfil = $this->profilRepo->findOneBy(['user_id' => $actualUser]);

        if (!$actualProfil) {
            return $this->json([
                "status" => "error",
                "message" => "Profil introuvable",
            ], 404);
        }

        // Mise à jour de la bio si elle est fournie
        $bio = $request->request->get('bio');
        if ($bio !== null) {
            $actualProfil->setBio($bio);
        }

        // Mise à jour du pseudo si fourni
        $pseudo = $request->request->get('pseudo');
        if ($pseudo !== null && $pseudo !== '') {
            $actualUser->setPseudo($pseudo);
            $this->em->persist($actualUser);
        }

        // upload image profil
        $file = $request->files->get('imageProfil');
        if ($file) {
            $filesystem = new Filesystem();
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/profiles';

            // Supprimer l'ancienne image si elle existe
            $oldFilename = $actualProfil->getImageProfil();

            if ($oldFilename && $filesystem->exists($uploadDir . '/' . $oldFilename)) {
                $filesystem->remove($uploadDir . '/' . $oldFilename);
            }

            // Génération d'un nom unique et déplacement du fichier
            $newFilename = uniqid() . '.' . $file->guessExtension();
            try {
                $file->move($uploadDir, $newFilename);
                $actualProfil->setImageProfil($newFilename);
            } catch (FileException $e) {
                return $this->json([
                    "status" => "error",
                    "message" => "Erreur lors de l'upload de l'image"
                ], 500);
            }
        }

        // Validation par symfony
        $errors = $validator->validate($actualProfil);
        if (count($errors) > 0) {
            return $this->json([
                "status" => "error",
                "message" => "Erreur de validation",
                "errors" => (string) $errors
            ], 400);
        }

        $this->em->persist($actualProfil);
        $this->em->flush();

        return $this->json([
            "status" => "ok",
            "message" => "Mise à jour du profil réussie",
            "result" => $actualProfil,
        ], 200, [], ['groups' => ['profil:read']]);
    }
}
