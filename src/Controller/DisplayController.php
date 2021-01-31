<?php

namespace App\Controller;

use App\Repository\UploadRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DisplayController extends AbstractController
{
    /**
     * @Route("/display/{id}", name="display_image")
     */
    public function index(int $id, UploadRepository $images, UserRepository $users): Response
    {
        $image = $images->findOneBy(['id' => $id]);

        if ($image == null)
            return $this->render('display/error.html.twig');
        else
        {
            // Récupérer les infos de l'image
            $nom = $image->getName();
            $proprietaireID = $image->getProprietaire();
            $auteur = $users->findOneBy(['id' => $proprietaireID])->getUsername();

            // Récupérer l'utilisateur actuel
            $userName = $this->getUser()->getUsername();
            $userPassword = $this->getUser()->getPassword();
            $clientID = $users->findOneBy(['username' => $userName, 'password' => $userPassword])->getId();

            // Vérifier si l'utilisateur est l'auteur pour afficher le bouton Supprimer l'image
            $proprietaire = false;
            if ($proprietaireID == $clientID)
                $proprietaire = true;

            // Vérifier si l'image est publique pour l'afficher ou non aux autres utilisateurs
            // La rendre visible tout de même si l'auteur est l'utilisateur actuel
            $public = $image->getPublic();
            if ($proprietaire)
                $public = true;

            if ($public)
                return $this->render('display/view.html.twig', [
                    "nom" => $nom,
                    "auteur" => $auteur,
                    "id" => $id,
                    "showDelete" => $proprietaire
                ]);
            else
                return $this->render('display/private.html.twig');
        }
    }

    /**
     * @Route("/display/remove/{id}", name="delete_image")
     */
    public function removeImage(int $id, UploadRepository $images, EntityManagerInterface $manager)
    {
        // Récupérer l'entité
        $image = $images->findOneBy(['id' => $id]);

        // Retirer l'entité du dossier du site
        $nom = $image->getName();
        unlink(__DIR__ . "/../../public/uploads/" . $nom);

        // Retirer l'entité du database
        $manager->remove($image);
        $manager->flush();

        // Ramener le client au menu
        return $this->render('home/index.html.twig');
    }
}
