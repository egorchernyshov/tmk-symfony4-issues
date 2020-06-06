<?php

namespace App\Tests\Unit\Repository;

use App\Repository\CommonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use PHPUnit\Framework\TestCase;

class CommonRepositoryTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject */
    private $em;

    /** @var CommonRepository */
    private $repository;

    protected function setUp()
    {
        parent::setUp();

        $this->em = $this->createMock(EntityManagerInterface::class);
        $this->repository = new CommonRepository($this->em);
    }

    public function testInsert()
    {
        $entity = new Entity();
        $this->setEMPersistMock($entity, 1);
        $this->setEMFlushMock(1);

        $this->repository->insert($entity);
    }

    public function testDelete()
    {
        $entity = new Entity();
        $this->setEMRemoveMock($entity, 1);
        $this->setEMFlushMock(1);

        $this->repository->delete($entity);
    }

    public function testAddAndSave()
    {
        $entity = new Entity();
        $this->setEMPersistMock($entity, 3);
        $this->setEMFlushMock(1);

        $this->repository->add($entity);
        $this->repository->add($entity);
        $this->repository->add($entity);
        $this->repository->save();
    }

    public function testPersist()
    {
        $entity = new Entity();
        $this->setEMPersistMock($entity, 1);
        $this->setEMFlushMock(0);

        $this->repository->persist($entity);
    }

    private function setEMRemoveMock($entity, int $count = 1)
    {
        $this->em
            ->expects($this->exactly($count))
            ->method('remove')
            ->with($this->equalTo($entity));
    }

    private function setEMFlushMock(int $count = 1)
    {
        $this->em
            ->expects($this->exactly($count))
            ->method('flush');
    }

    private function setEMPersistMock($entity, int $count = 1)
    {
        $this->em
            ->expects($this->exactly($count))
            ->method('persist')
            ->with($this->equalTo($entity));
    }
}
