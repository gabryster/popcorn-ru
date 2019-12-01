<?php

namespace App\Command;

use App\Processors\ForumProcessor;
use App\Processors\TopicProcessor;
use App\Service\MovieInfo;
use Enqueue\Client\Message;
use Enqueue\Client\ProducerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tmdb\Model\Movie;
use Tmdb\Repository\MovieRepository;

class TestTmdbCommand extends Command
{
    protected static $defaultName = 'test:tmdb';

    /**
     * @var MovieInfo
     */
    private $movieInfo;

    /**
     * @var ProducerInterface
     */
    private $producer;

    public function __construct(MovieInfo $movieInfo, ProducerInterface $producer)
    {
        parent::__construct();
        $this->movieInfo = $movieInfo;
        $this->producer = $producer;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->producer->sendEvent(TopicProcessor::TOPIC, new Message(json_encode([
            'spider' => 'NnmClub',
            'topicId' => '1282399',
            'info' => ['seed' => '10', 'leech' => '1'],
        ])));
        // $this->movieInfo->fetchToLocal('tt0167261');
        // $this->movieInfo->fetchToLocal('tt0076759');
        // $this->movieInfo->fetchToLocal('tt0241527');

        return 0;
    }
}