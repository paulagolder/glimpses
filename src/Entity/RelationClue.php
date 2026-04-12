<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RelationClueRepository")
 * @ORM\Table(name="relationclue")
 */
class RelationClue
{


    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     */
    private $relationref;


    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     */
    private $glimpseref;


    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $text;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $contributor;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedt;

    public function getRelationRef(): ?int
    {
        return $this->relationref;
    }

    public function setRelationRef(int $ref): self
    {
        $this->relationref= $ref;
        return $this;
    }

   public function getGlimpseRef(): ?int
    {
        return $this->glimpseref;
    }

    public function setGlimpseRef(int $ref): self
    {
        $this->glimpseref= $ref;
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
