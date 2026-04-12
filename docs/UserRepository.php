<?php

namespace Foo\Entity;

use Doctrine\ORM\EntityRepository;
use Foo\LikeQueryHelpers;

class ProductRepository extends EntityRepository
{
    use LikeQueryHelpers;

    /**
     * Find Product entities containing searched terms
     *
     * @param string $term
     * @return Product[]
     */
    public function findInSearchableFields($term)
    {
        return $this->createQueryBuilder('p')
            ->where("p.title LIKE :title ESCAPE '!'")
            ->setParameter('title', $this->makeLikeParam($term))
            ->getQuery()
            ->execute();
    }
}
