<?php

namespace App\Entity;




class Predicate
{




    private $predicateref;
    private $roleref;
    private $glimpseid;
    private $verb;
    private $object;
    private $contributor;
    private $updatedt;



 public function getPredicateref(): ?int
    {
        return $this->predicateref;
    }

   public function setPredicateref(int $ref): self
    {
        $this->predicateref= $ref;

        return $this;
    }

    public function getroleref(): ?int
    {
        return $this->roleref;
    }

    public function setroleref(int $ref): self
    {
        $this->roleref= $ref;

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




    public function getVerb(): ?string
    {
        return $this->verb;
    }


    public function setVerb(string $verb): self
    {
        $this->verb = $verb;

        return $this;
    }


    public function getObject(): ?string
    {
        return $this->object;
    }


    public function setObject(string $object): self
    {
        $this->object = $object;

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
