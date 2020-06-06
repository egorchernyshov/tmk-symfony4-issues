<?php

namespace App\Tests\Functional\Command;

use App\Command\DbImportJsonCommand;
use App\Entity\Product;
use App\Repository\CommonRepository;
    use Generator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CommandTest extends KernelTestCase
{
    /**
     * @dataProvider jsonFileProvider
     */
    public function test(
        string $file,
        string $content,
        int $validationCount,
        int $addCount,
        int $saveCount
    ) {
        $absolutePath = $this->createFileWithContent($file, $content);
        $input = new ArrayInput([DbImportJsonCommand::FILE_PATH_ARGUMENT => $absolutePath]);
        $output = new BufferedOutput();

        $command = new DbImportJsonCommand(
            $this->getCommonRepositoryMock($addCount, $saveCount),
            $this->getValidatorMock($validationCount),
            $this->getSerializerMock($this->getItemsCollection($addCount))
        );
        $returnCode = $command->run($input, $output);

        self::assertStringContainsString('[OK] Success!', $output->fetch());
        self::assertEquals(0, $returnCode);

        $this->removeFile($absolutePath);
    }

    public function jsonFileProvider(): ?Generator
    {
        yield 'json data of categories' => [
            'categories.json',
            '[
                {"eId": 1, "title": "Category 1"},
                {"eId": 2, "title": "Category 2"},
                {"eId": 2, "title": "Category 33333333"}
            ]',
            'validation invoked count times' => 4,
            'repository add method invoked count times' => 3,
            'repository save method invoked count times' => 1,
        ];

        yield 'json data of products' => [
            'file name' => 'products.json',
            'json content' => '[
                {"eId": 1, "title": "Product 1", "price": 101.01, "categoriesEId": [1, 2]},
                {"eId": 2, "title": "Product 2", "price": 199.01, "categoryEId": [2, 3]},
                {"eId": 3, "title": "Product 33333333", "price": 999.01, "categoryEId": [3, 1]}
            ]',
            'validation invoked count times' => 4,
            'repository add method invoked count times' => 3,
            'repository save method invoked count times' => 1,
        ];
    }

    protected function createFileWithContent(string $file, string $content): string
    {
        $absolutePath = sys_get_temp_dir() . '/' . $file;
        file_put_contents($absolutePath, $content);

        return $absolutePath;
    }

    protected function removeFile(string $absolutePath): bool
    {
        return @unlink($absolutePath);
    }

    private function getItemsCollection(int $count)
    {
        return array_fill(0, $count, new Product());
    }

    protected function getCommonRepositoryMock(int $addCount, int $saveCount)
    {
        $repository = $this->createMock(CommonRepository::class);
        $repository
            ->expects($this->exactly($addCount))
            ->method('add');

        $repository
            ->expects($this->exactly($saveCount))
            ->method('save');

        return $repository;
    }

    protected function getValidatorMock(int $count)
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $violations = $this->createMock(ConstraintViolationListInterface::class);
        $validator
            ->expects($this->exactly($count))
            ->method('validate')
            ->willReturn($violations);

        return $validator;
    }

    protected function getSerializerMock(array $collection = [])
    {
        $serializer = $this->createMock(SerializerInterface::class);
        $serializer
            ->method('deserialize')
            ->willReturn($collection);

        return $serializer;
    }
}
