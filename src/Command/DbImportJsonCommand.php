<?php

namespace App\Command;

use App\Entity\Category;
use App\Entity\Product;
use App\Repository\CommonRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function file_get_contents;

final class DbImportJsonCommand extends Command
{
    private const TYPES = [
        'products' => Product::class,
        'categories' => Category::class,
    ];

    public const FILE_PATH_ARGUMENT = 'file-path';

    protected static $defaultName = 'app:db:import-from-file';

    /** @var CommonRepository */
    private $commonRepository;

    /** @var ValidatorInterface */
    private $validator;

    /** @var SerializerInterface */
    private $serializer;

    /** @var SymfonyStyle */
    private $io;

    public function __construct(
        CommonRepository $commonRepository,
        ValidatorInterface $validator,
        SerializerInterface $serializer,
        string $name = null
    ) {
        parent::__construct($name);
        $this->validator = $validator;
        $this->serializer = $serializer;
        $this->commonRepository = $commonRepository;
    }

    protected function configure()
    {
        $this
            ->setDescription('Import json-data from file to database')
            ->addArgument(self::FILE_PATH_ARGUMENT, InputArgument::REQUIRED, 'Path to json-file');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);
        $pathToFile = $input->getArgument(self::FILE_PATH_ARGUMENT);

        $file = new File($pathToFile);
        $json = $this->getJsonContent($file);

        $name = $file->getBasename('.' . $file->getExtension());
        if (! isset(self::TYPES[$name])) {
            throw new \RuntimeException(sprintf('Import for "%s" not supported', $name));
        }

        $collection = $this->serializer->deserialize($json, $this->getType($name), 'json');
        $this->persist($collection);

        $this->io->success('Success!');

        return 0;
    }

    /**
     * @param File $file
     *
     * @return false|string
     */
    private function getJsonContent(File $file)
    {
        $json = file_get_contents($file->getRealPath());

        $violations = $this->validator->validate($json, new Constraints\Json());
        if ($violations->count() > 0) {
            throw new RuntimeException($violations);
        }

        return $json;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function getType(string $name)
    {
        return sprintf('%s[]', self::TYPES[$name]);
    }

    /**
     * @param array $collection
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function persist(array $collection): void
    {
        foreach ($collection as $item) {
            $violations = $this->validator->validate($item);
            if ($violations->count() > 0) {
                $this->writeViolations($violations);
                continue;
            }

            $this->commonRepository->add($item);
        }

        $this->commonRepository->save();
    }

    /**
     * @param ConstraintViolationListInterface $violations
     */
    private function writeViolations(ConstraintViolationListInterface $violations): void
    {
        foreach ($violations as $violation) {
            $this->io->warning($violation->getMessage());
        }
    }
}
