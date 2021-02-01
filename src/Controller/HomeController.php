<?php

namespace App\Controller;

use App\Repository\UploadRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class HomeController extends AbstractController
{
    /**
     * HomeController constructor.
     * @var TokenInterface|null
     */
    private $token;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->token = $tokenStorage->getToken();
    }

    public function getToken(): ?TokenInterface
    {
        return $this->token;
    }


    /**
     * @Route("/", name="home")
     */
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

    public function chercherPhotos(UserRepository $clients, UploadRepository $images): Response
    {
        // 1 - Récupérer l'id du propriétaire de l'image
        $clientID = $clients->findOneBy([
            'username' => $this->getUser()->getUsername(),
            'password' => $this->getUser()->getPassword()
        ])->getId();

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

    /**
     * @Route("/profil", name="liste_profil")
     */
    public function showListe(UserRepository $clients): Response
    {
        $token = $this->getToken();
        $listeClients = null;

        if (null !== $token) {
            $client = $token->getUser();
            $listeClients = $clients->findUsersWithoutConnect($client);
        }

        return $this->render('home/liste.html.twig', ['liste' => $listeClients]);
    }

    /**
     * @Route("/profil/{id}", name="voir_profil")
     */
    public function showProfil(int $id, UserRepository $clients): Response
    {
        $client = $clients->findOneBy(['id' => $id]);
        return $this->render("home/profil.html.twig", ["client" => $client]);
    }

    /**
     * @Route("/profil/photos/{id}", name="voir_profil_photos")
     */
    public function photosUtilisateur(int $id, UploadRepository $imageRepository): Response
    {
        // 1 - Récupérer les images public
        $reposiroty = $imageRepository->findBy(['proprietaire' => $id, 'public' => true]);

        // 2 - Récupérer le dossier des images
        $uploadsDirectory = scandir(__DIR__ . "/../../public/uploads");

        // 3 - Récupérer la liste des noms
        $repositoryNoms = [];
        $slot = 0;
        foreach ($reposiroty as $image)
        {
            $repositoryNoms[$slot] = $image->getName();
            $slot++;
        }

        // 4 - Récupérer la liste des id
        $repositoryIDs = [];
        $slot = 0;
        foreach ($reposiroty as $image)
        {
            $repositoryIDs[$slot] = $image->getId();
            $slot++;
        }

        // 5 - Extraire uniquement les images nécéssaires
        $listeAfficher = [];
        $slot = 0;
        foreach ($uploadsDirectory as $nomImage)
        {
            if (in_array($nomImage, $repositoryNoms))
            {
                $listeAfficher[$slot] = $nomImage;
                $slot++;
            }
        }

        return $this->render("home/voirImages.html.twig", [
            "liste" => $listeAfficher,
            "ID" => $repositoryIDs
        ]);
    }
}
