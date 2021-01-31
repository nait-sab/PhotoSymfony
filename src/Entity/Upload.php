<?php

namespace App\Entity;

use App\Repository\UploadRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=UploadRepository::class)
 */
class Upload
{
    // Variables

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $proprietaire;

    /**
     * @ORM\Column(type="boolean")
     */
    private $public;

    // Accesseurs
    public function getId(): ?int { return $this->id; }
    public function getName() { return $this->name; }
    public function getProprietaire() { return $this->proprietaire; }
    public function getPublic() { return $this->public; }

    // Modifieurs
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setProprietaire($name)
    {
        $this->proprietaire = $name;
        return $this;
    }

    public function setPublic($etat)
    {
        $this->public = $etat;
        return $this;
    }
}
