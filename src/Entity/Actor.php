<?php

namespace App\Entity;
use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ActorRepository")
 */
class Actor
{


    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $actorid;


    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $text;


     /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $name;


  /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $specifier;


    /**
     * @ORM\Column(type="string", length="20",nullable=true)
     */
    private $birthdate;


    /**
     * @ORM\Column(type="string", length="20",nullable=true)
     */
    private $ebirthdate;


    /**
     * @ORM\Column(type="string", length="20",nullable=true)
     */
    private $lbirthdate;


    /**
     * @ORM\Column(type="string", length="20",nullable=true)
     */
    private $ldeathdate;

    /**
     * @ORM\Column(type="string", length="20",nullable=true)
     */
    private $edeathdate;


    /**
     * @ORM\Column(type="string", length="20",nullable=true)
     */
    private $deathdate;




    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $contributor;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedt;


   private $roles;


     public function __construct() {
        $this->roles = new ArrayCollection();
    }


    public function getActorid(): ?int
    {
        return $this->actorid;
    }

    public function setActorid(int $ref): self
    {
        $this->actorid= $ref;

        return $this;
    }





    public function getText(): ?string
    {
        return $this->text;
    }


    public function setText($text): self
    {
        $this->text = $text;

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

    public function getSpecifier(): ?string
    {
        return $this->specifier;
    }


    public function setSpecifier($name): self
    {
        $this->specifier = $name;

        return $this;
    }


    public function getDeathdate(): ?string
    {
        return $this->deathdate;
    }


    public function setDeathdate(string $text): self
    {
        $this->deathdate = $text;
        return $this;
    }

    public function getlDeathdate(): ?string
    {
        return $this->ldeathdate;
    }


    public function setlDeathdate(string $text): self
    {
        $this->ldeathdate = $text;
        return $this;
    }

    public function geteDeathdate(): ?string
    {
        return $this->edeathdate;
    }


    public function seteDeathdate(string $text): self
    {
        $this->edeathdate = $text;
        return $this;
    }

    public function getlBirthdate(): ?string
    {
        return $this->lbirthdate;
    }


    public function setlBirthdate(string $text): self
    {
        $this->lbirthdate = $text;
        return $this;
    }

    public function geteBirthdate(): ?string
    {
        return $this->ebirthdate;
    }


    public function seteBirthdate(string $text): self
    {
        $this->ebirthdate = $text;
        return $this;
    }

    public function getBirthdate(): ?string
    {
        return $this->birthdate;
    }


    public function setBirthdate(string $text): self
    {
        $this->birthdate = $text;
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
