<?php

namespace App\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class CommonRepository
{
    /** @var ArrayCollection */
    private $collection;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->collection = new ArrayCollection();
        $this->entityManager = $entityManager;
    }

    /**
     * @param $entity
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function delete($entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }

    /**
     * @param $entity
     *
     * @return self
     */
    public function add($entity)
    {
        $this->collection->add($entity);

        return $this;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(): void
    {
        foreach ($this->collection as $entity) {
            $this->persist($entity);
        }

        $this->entityManager->flush();
    }

    /**
     * @param $entity
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function insert($entity): void
    {
        $this->persist($entity);
        $this->entityManager->flush();
    }

    /**
     * @param $entity
     *
     * @throws \Doctrine\ORM\ORMException
     *
     * @return self
     */
    public function persist($entity): self
    {
        $this->entityManager->persist($entity);

        return $this;
    }
}
