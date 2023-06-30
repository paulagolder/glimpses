<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SourceRepository")
 */
class Source
{



    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $sourceid;


    /**
     * @ORM\Column(type="text",  nullable=true)
     */
    private $title;


 /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $url;

 /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $region;

 /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $period;

     /**
     * @ORM\Column(type="string", length=12, nullable=true)
     */
    private $language;



  public function getSourceid(): ?int
    {
        return $this->sourceid;
    }

    public function setSourceid(int $id): self
    {
        $this->sourceid = $id;

        return $this;
    }



    public function getTitle(): ?string
    {
        return $this->title;
    }


    public function setTitle(string $text): self
    {
        $this->title = $text;

        return $this;
    }

     public function getUrl(): ?string
    {
        return $this->url;
    }


    public function setUrl(string $text): self
    {
        $this->url = $text;

        return $this;
    }


     public function getRegion(): ?string
    {
        return $this->region;
    }


    public function setRegion(string $text): self
    {
        $this->region = $text;

        return $this;
    }


     public function getPeriod(): ?string
    {
        return $this->period;
    }


    public function setPeriod(string $text): self
    {
        $this->period = $text;

        return $this;
    }

     public function getLanguage(): ?string
    {
        return $this->language;
    }


    public function setLanguage(string $text): self
    {
        $this->language = $text;

        return $this;
    }

}
