<?php

declare(strict_types=1);

namespace AkeneoEtl\Tests\Shared\Command;

use AkeneoEtl\Domain\EtlProcess;
use AkeneoEtl\Domain\Profile\EtlProfile;
use AkeneoEtl\Domain\Resource\Resource;
use AkeneoEtl\Infrastructure\Command\ResourceComparer;
use AkeneoEtl\Infrastructure\EtlFactory;
use AkeneoEtl\Tests\Acceptance\bootstrap\InMemoryExtractor;
use AkeneoEtl\Tests\Acceptance\bootstrap\InMemoryLoader;
use LogicException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Yaml\Yaml;
use Twig\Environment;

final class GenerateDocsCommand extends Command
{
    private EtlFactory $factory;

    private Environment $twig;

    private EventDispatcherInterface $eventDispatcher;

    private ResourceComparer $resourceComparer;

    public function __construct(
        EtlFactory $factory,
        Environment $twig,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->factory = $factory;
        $this->eventDispatcher = $eventDispatcher;
        $this->twig = $twig;

        $this->resourceComparer = new ResourceComparer();

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('generate-docs');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $examples = Yaml::parseFile('docs/use-case-provider.yaml');

        foreach ($examples as &$example) {
            foreach ($example['tasks'] as &$task) {
                $profileData = $task['profile'];

                $profile = EtlProfile::fromArray($profileData);
                $resource = Resource::fromArray($task['resource'], 'product');

                $extractor = new InMemoryExtractor($resource);
                $loader = new InMemoryLoader($resource, false);
                $transformer = $this->factory->createTransformer($profile);

                $etl = new EtlProcess($extractor, $transformer, $loader, $this->eventDispatcher);
                $etl->execute();

                if ($loader->getResult() === null) {
                    throw new LogicException('Task %s: invalid rules', $task['description']);
                }

                $compareTable = $this->getCompareTable($loader->getResult());

                foreach ($compareTable as $change) {
                    $task['results'][$change[1]] = [
                        'field' => $change[1],
                        'before' => $change[2],
                        'after' => $change[3],
                    ];
                }

                $task['profile'] = Yaml::dump($task['profile'], 4);
            }


            $content = $this->twig->render('use-case.md.twig', $example);

            $resultFileName = sprintf('docs/examples/%s.md', $example['file_name']);
            file_put_contents($resultFileName, $content);
        }

        return Command::SUCCESS;
    }

    private function getCompareTable(Resource $resource): array
    {
        if ($resource->getOrigin() === null) {
            return $this->resourceComparer->getCompareTable(null, $resource);
        }

        return $this->resourceComparer->getCompareTable(
            $resource->getOrigin()->diff($resource),
            $resource->diff($resource->getOrigin())
        );
    }
}
