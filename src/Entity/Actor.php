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
     *
     * @ORM\GeneratedValue()
     *
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
     * @ORM\Column(type="text", nullable=true)
     */
    private $location;

    /**
     * @ORM\Column(type="string", length="20",nullable=true)
     */
    private $birthdate;

    /**
     * @ORM\Column(type="string", length="20",nullable=true)
     */
    private $deathdate;

    /**
     * @ORM\Column(type="string", length="10",nullable=true)
     */
    private $gender;

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

     /**
       * @ORM\Column(type="string", length=20, nullable=true)
    */
     private $tag;

     /**
        * @ORM\Column(type="text",  nullable=true)
     */
     private $note;

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
        $this->actorid = $ref;

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
        if ('<' == substr($this->birthdate, 0, 1) or '>' == substr($this->birthdate, 0, 1) or '~' == substr($this->birthdate, 0, 1)) {
            $birthyear = substr($this->birthdate, 0, 5);
        } else {
            $birthyear = substr($this->birthdate, 0, 4);
        }
        if ('<' == substr($this->deathdate, 0, 1) or '>' == substr($this->deathdate, 0, 1) or '~' == substr($this->deathdate, 0, 1)) {
            $deathyear = substr($this->deathdate, 0, 5);
        } else {
            $deathyear = substr($this->deathdate, 0, 4);
        }

        return $this->surname.', '.$this->forename.' ('.$birthyear.'-'.$deathyear.')';
    }

    public function getName(): ?string
    {
        return $this->surname.', '.$this->forename;
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

  public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation($name): self
    {
        $this->location = $name;

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

    public function getDates(): ?string
    {
        $text = '('.$this->birthdate.'-'.$this->deathdate.')';

        return $text;
    }

 public function getTag(): ?string
  {
        return $this->tag;
  }

  public function setTag(string $txt): self
  {
     $this->tag = $txt;
     return $this;
  }

 public function getNote(): ?string
  {
        return $this->note;
  }

  public function setNote(string $txt): self
  {
     $this->note = $txt;
     return $this;
  }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $text): self
    {
        if ('f' == substr($text, 0, 1) or 'F' == substr($text, 0, 1)) {
            $this->gender = 'female';
        } else {
            $this->gender = 'male';
        }

        return $this;
    }

    public function getGenderSymbol(): ?string
    {
        if ('female' == $this->gender) {
            return 'F';
        }
        if ('male' == $this->gender) {
            return 'M';
        }

        return 'X';
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
        if (0 == strlen(trim($this->text))) {
            $this->text = $actor2->text;
        } elseif (!strcasecmp($this->text, $actor2->text)) {
            $this->text .= '+T+'.$actor2->text;
        }
        if (0 == strlen(trim($this->specifier))) {
            $this->specifier = $actor2->specifier;
        } elseif (!strcasecmp($this->specifier, $actor2->specifier)) {
            $this->specifier .= '++'.$actor2->specifier;
        }
        if (0 == strlen(trim($this->surname))) {
            $this->surname = $actor2->surname;
        } elseif (!strcasecmp($this->surname, $actor2->surname)) {
            $this->text .= '+S+'.$actor2->surname;
        }
        if (0 == strlen(trim($this->forename))) {
            $this->forename = $actor2->forename;
        } elseif (!strcasecmp($this->forename, $actor2->forename)) {
            $this->text .= '+F+'.$actor2->forename;
        }
        if (0 == strlen(trim($this->birthdate))) {
            $this->birthdate = $actor2->birthdate;
        } elseif (!strcasecmp($this->birthdate, $actor2->birthdate)) {
            $this->text .= '+B+'.$actor2->birthdate;
        }
        if (0 == strlen(trim($this->deathdate))) {
            $this->deathdate = $actor2->deathdate;
        } elseif (!strcasecmp($this->deathdate, $actor2->deathdate)) {
            $this->text .= '+D+'.$actor2->deathdate;
        }
    }
}
