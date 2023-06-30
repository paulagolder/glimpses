<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PersonRepository")
 */
class Person
{


    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $personref;

    /**
     * @ORM\Column(type="integer")
     */
    private $glimpseid;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $text;


 /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $role;


     /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $contributor;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedt;





    public function getPersonref(): ?int
    {
        return $this->personref;
    }

    public function setPersonref(int $ref): self
    {
        $this->personref= $ref;

        return $this;
    }


   public function getGlimpseid(): ?int
    {
        return $this->glimpseid;
    }

    public function setGlimpseid(int $ref): self
    {
        $this->glimpseid= $ref;

        return $this;
    }


    public function getText(): ?string
    {
        return $this->text;
    }


    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }


    public function setRole(string $role): self
    {
        $this->role = $role;

        return $this;
    }


    public function getName(): ?string
    {
        return $this->name;
    }


    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }





    public function getContributor(): ?string
    {
        return $this->contributor;
    }

    public function setContributor(?string $contributor): self
    {
        $this->contributor = $contributor;

        return $this;
    }

    public function getUpdateDt(): ?\DateTimeInterface
    {
        return $this->updatedt;
    }



    public function setUpdateDt(?\DateTimeInterface $updatedt): self
    {
        $this->updatedt = $updatedt;

        return $this;
    }
}
