<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GlimpseRepository")
 */
class Glimpse
{


    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $glimpseid;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $text;

    /**
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $language;


        /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $location;


        /**
     * @ORM\Column(type="string", length=12, nullable=true)
     */
    private $type;



        /**
     * @ORM\Column(type="string", length=12, nullable=true)
     */
    private $date;


              /**
     * @ORM\Column(type="string", length=12, nullable=true)
     */
    private $datequalifier;

                /**
     * @ORM\Column(type="integer",  nullable=true)
     */
    private $sourceid;

            /**
     * @ORM\Column(type="text",  nullable=true)
     */
    private $source;

            /**
     * @ORM\Column(type="string", length=12, nullable=true)
     */
    private $ref;


    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $contributor;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedt;


    public $roles;


    public function getGlimpseid(): ?int
    {
        return $this->glimpseid;
    }

    public function setGlimpseid(int $glimpseid): self
    {
        $this->glimpseid = $glimpseid;

        return $this;
    }

     public function getSourceid()
    {
        return $this->sourceid;
    }

    public function setSourceid( $sourceid): self
    {
        $this->sourceid = $sourceid;

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


    public function getLanguage(): ?string
    {
        return $this->language;
    }

    public function setLanguage(?string $lang): self
    {
        $this->language = $lang;

        return $this;
    }

      public function getLocation(): ?string
    {
        return $this->location;
    }


    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }



      public function getType(): ?string
    {
        return $this->type;
    }


    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }


      public function getSource(): ?string
    {
        return $this->source;
    }


    public function setSource(string $text): self
    {
        $this->source = $text;

        return $this;
    }

        public function getRef(): ?string
    {
        return $this->ref;
    }


    public function setRef(string $text): self
    {
        $this->ref = $text;

        return $this;
    }


      public function getDate(): ?string
    {
        return $this->date;
    }


    public function setDate(string $dt)
    {
     $this->datequalifier = null;
     if (!$dt)
     {
        $this->date =  " no date ";
        return;
    }
      $dt =  str_replace(' y ', ' ', $dt);
        $dt =  str_replace(' ye ', ' ', $dt);
          $dt =  str_replace('th ', ' ', $dt);
    $dt =  str_replace('(', '', $dt);
    $dt =  str_replace(')', '', $dt);
    if( stripos($dt, ':') !== false )
    {
       $datestruct = explode( ":",$dt );
       dump($datestruct);
       $this->datequalifier = trim($datestruct[0]);
       $dt = trim($datestruct[01]);
    }
    try
    {
        new \DateTime($dt);
         $this->date = date('Y-m-d', strtotime($dt));
    } catch (\Exception $e)
    {
       $this->date = $dt;
    }
}

  public function getDateQualifier(): ?string
    {
        return $this->datequalifier;
    }


    public function setDateQualifier(string $type): self
    {
        $this->datequalifier = $type;

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


    public function setRoles($roles)
    {
      $this->roles = $roles;
    }

     public function getRoles()
    {
     return  $this->roles;
    }
}
