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
    private $forename;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $surname;

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
    private $deathdate;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $contributor;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedt;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $keywords;

   //private $roles;

     public function __construct()
     {
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

    public function getForename(): ?string
    {
        return $this->forename;
    }

    public function setForename(string $name): self
    {
        $this->forename = $name;
        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function getLabel(): ?string
    {
        return $this->surname.", ".$this->forename." (".$this->birthdate."-".$this->deathdate.")";
    }

    public function setSurname(string $name): self
    {
        $this->surname = $name;
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

    public function getKeywords(): ?string
    {
        return $this->keywords;
    }

    public function setKeywords(string $name): self
    {
        $this->keywords = $name;
        return $this;
    }

    public function merge($actor2)
    {

        if (strlen(trim($this->text)) == 0)
        {
            $this->text =$actor2->text;
        }
        else if(!strcasecmp($this->text, $actor2->text))
        {
            $this->text .= "+T+". $actor2->text;
        }
        if (strlen(trim($this->specifier)) == 0)
        {
            $this->specifier =$actor2->specifier;
        }
        else if(!strcasecmp($this->specifier, $actor2->specifier))
        {
            $this->specifier.= "++". $actor2->specifier;
        }
        if (strlen(trim($this->surname)) == 0)
        {
            $this->surname =$actor2->surname;
        }
        else if(!strcasecmp($this->surname, $actor2->surname))
        {
            $this->text .= "+S+". $actor2->surname;
        }
        if (strlen(trim($this->forename)) == 0)
        {
            $this->forename =$actor2->forename;
        }
        else if(!strcasecmp($this->forename, $actor2->forename))
        {
            $this->text .= "+F+". $actor2->forename;
        }
        if (strlen(trim($this->birthdate)) == 0)
        {
            $this->birthdate =$actor2->birthdate;
        }
        else if(!strcasecmp($this->birthdate, $actor2->birthdate))
        {
            $this->text .= "+B+". $actor2->birthdate;
        }
        if (strlen(trim($this->deathdate)) == 0)
        {
            $this->deathdate =$actor2->deathdate;
        }
        else if(!strcasecmp($this->deathdate, $actor2->deathdate))
        {
            $this->text .= "+D+". $actor2->deathdate;
        }
    }
}
