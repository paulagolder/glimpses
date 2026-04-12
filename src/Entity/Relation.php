<?php

namespace App\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RelationRepository")
 */

class Relation
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $relationid;


    /**
     * @ORM\Column(type="integer")
     **/
    private $actor1ref;


    /**
     * @ORM\Column(type="text")
     **/
    private $relation;


    /**
     * @ORM\Column(type="integer")
     **/
    private $actor2ref;


    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $clues;


    public function getRelationId(): ?int
    {
        return $this->relationid;
    }

   public function setRelationiId(int $ref): self
    {
        $this->relationid= $ref;
        return $this;
    }

    public function getActor1Ref(): ?int
    {
        return $this->actor1ref;
    }

    public function setActor1Ref(int $ref): self
    {
        $this->actor1ref= $ref;
        return $this;
    }

    public function getActor2Ref(): ?int
    {
        return $this->actor2ref;
    }

    public function setActor2Ref(int $ref): self
    {
        $this->actor2ref= $ref;
        return $this;
    }

    public function getRelation(): ?string
    {
        return $this->relation;
    }

    public function setRelation(string $text): self
    {
        $this->relation = $text;
        return $this;
    }

    public function getClues(): ?string
    {
        return $this->clues;
    }


    public function setClues(string $text): self
    {
        $this->clues = $text;
        return $this;
    }

    public function setConfidence(int $num): self
    {
        $this->confidence= $num;
        return $this;
    }

    public function getConfidence(): int
    {
        return $this->confidence;
    }



}
