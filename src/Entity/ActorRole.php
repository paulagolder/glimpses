<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ActorRoleRepository")
 * @ORM\Table(name="actorrole")
 */
class ActorRole
{


    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     */
    private $actorref;


    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     */
    private $roleref;


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

    public function getActorRef(): ?int
    {
        return $this->actorref;
    }

    public function setActorRef(int $ref): self
    {
        $this->actorref= $ref;
        return $this;
    }

   public function getRoleRef(): ?int
    {
        return $this->roleref;
    }

    public function setRoleRef(int $ref): self
    {
        $this->roleref= $ref;
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
