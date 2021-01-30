<?php

namespace App\Controller;

use App\Repository\UploadRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'username' => '',
        ]);
    }

    public function chercherPhotos(UserRepository $clients, UploadRepository $images): Response
    {
        // 1 - Récupérer l'id du propriétaire de l'image
        $userName = $this->getUser()->getUsername();
        $userPassword = $this->getUser()->getPassword();
        $clientID = $clients->findOneBy(['username' => $userName, 'password' => $userPassword])->getId();

        // 2 - Récupérer le dossier des images
        $repo = scandir(__DIR__ . "/../../public/uploads");

        // 3 - Récupérer la liste des images du client
        $repoClient = $images->findBy(['proprietaire' => $clientID]);

        // 4 - Récupérer la liste des noms
        $listeImages = [];
        $slot = 0;
        foreach ($repoClient as $image)
        {
            $listeImages[$slot] = $image->getName();
            $slot++;
        }

        // 5 - Récupérer la liste des id
        $listeID = [];
        $slot = 0;
        foreach ($repoClient as $image)
        {
            $listeID[$slot] = $image->getId();
            $slot++;
        }

        // 6 - Extraire uniquement les images apartenant au client
        $listeVisible = [];
        $slot = 0;
        foreach ($repo as $image)
        {
            if (in_array($image, $listeImages))
            {
                $listeVisible[$slot] = $image;
                $slot++;
            }
        }

        return $this->render("home/voirImages.html.twig", ["liste" => $listeVisible, "ID" => $listeID]);
    }

    /**
     * @Route("/image/{nom}", name="afficher_image")
     */
    public function affiche(string $nom): Response
    {
        return $this->file(__DIR__ . "/../../public/uploads/${nom}");
    }
}
