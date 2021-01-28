<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Upload;
use App\Form\UploadType;
use App\Repository\UserRepository;

class ImportationController extends AbstractController
{
    /**
     * @Route("/importation", name="importation_page")
     */
    public function index(Request $requete, UserRepository $repo, EntityManagerInterface $manager)
    {
        // 1 - Récupérer l'id du propriétaire de l'image
        $userName = $this->getUser()->getUsername();
        $userPassword = $this->getUser()->getPassword();
        $user = $repo->findOneBy(['username' => $userName, 'password' => $userPassword])->getId();

        // 2 - Init du fichier et formulaire
        $upload = new Upload();
        $formulaire = $this->createForm(UploadType::class, $upload);
        $formulaire->handleRequest($requete);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            // 3 - Récupération du fichier
            $fichier = $upload->getName();
            $fichierNom = md5(uniqid()) . '.' . $fichier->guessExtension();
            $fichier->move($this->getParameter('upload_directory'), $fichierNom);

            // 4 - Création du fichier sur le SQL
            $upload->setName($fichierNom);
            $upload->setProprietaire($user);
            $manager->persist($upload);
            $manager->flush();

            return $this->redirectToRoute('home');
        }

        return $this->render('importation/importation.html.twig', ['formulaire' => $formulaire->createView()]);
    }
}
