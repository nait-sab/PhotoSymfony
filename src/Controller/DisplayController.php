<?php

namespace App\Controller;

use App\Repository\UploadRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
            $nom = $image->getName();
            $proprietaireID = $image->getProprietaire();
            $user = $users->findOneBy(['id' => $proprietaireID])->getUsername();

            return $this->render('display/view.html.twig', ["nom" => $nom, "auteur" => $user]);
        }
    }
}
