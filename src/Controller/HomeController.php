<?php

namespace App\Controller;

use App\Repository\UploadRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityNotFoundException;
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

    public function chercherPhotos(UserRepository $clients, UploadRepository $imageRepository, TokenStorageInterface $tokenStorage): Response
    {
        $images = null;
        if ( null !== $tokenStorage->getToken() ) {
            $user = $tokenStorage->getToken()->getUser();

            $images = $imageRepository->findBy([ "proprietaire" => $user->getId()]);
        }

        return $this->render("home/voirImages.html.twig", ["images" => $images]);
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
        $client = $clients->find($id);

        if (null === $client) {
            throw new EntityNotFoundException('Utilisateur non trouvé !');
        }

        return $this->render("home/profil.html.twig", ["client" => $client]);
    }

    /**
     * @Route("/profil/photos/{id}", name="voir_profil_photos")
     */
    public function photosUtilisateur(UploadRepository $imageRepository, UserRepository $userRepository, int $id): Response
    {
        $user = $userRepository->find($id);

        if (null === $user) {
            throw new EntityNotFoundException('Utilisateur non trouvé !');
        }

        $images = $imageRepository->findBy([ "proprietaire" => $user->getId(), "public" => true]);

        return $this->render("home/voirImages.html.twig", [
            "images" => $images
        ]);
    }
}
