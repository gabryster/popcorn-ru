<?php

namespace App\Command;

use App\Processors\ForumProcessor;
use App\Processors\SyncProcessor;
use App\Repository\MovieRepository;
use App\Repository\ShowRepository;
use App\Repository\TorrentRepository;
use App\Service\SpiderSelector;
use Enqueue\Client\Message;
use Enqueue\Client\ProducerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncCommand extends Command
{
    protected static $defaultName = 'sync:old';

    /** @var ProducerInterface */
    private $producer;

    /**
     * @var TorrentRepository
     */
    private $torrentRepository;

    /**
     * @var MovieRepository
     */
    private $movieRepository;

    /**
     * @var ShowRepository
     */
    private $showRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        TorrentRepository $torrentRepository,
        MovieRepository $movieRepository,
        ShowRepository $showRepository,
        ProducerInterface $producer,
        LoggerInterface $logger
        )
    {
        parent::__construct();
        $this->producer = $producer;
        $this->torrentRepository = $torrentRepository;
        $this->movieRepository = $movieRepository;
        $this->showRepository = $showRepository;
        $this->logger = $logger;
    }

    protected function configure()
    {
        // 180 дней по 200 каждые 4 часа (???)
        $this
            ->setDescription('Generate task for sync old records')
            ->addArgument('days', InputArgument::REQUIRED, 'Days for old')
            ->addArgument('limit', InputArgument::REQUIRED, 'Limit')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $days = $input->getArgument('days');
        $limit = $input->getArgument('limit');

        $torrents = $this->torrentRepository->getOld(new \DateTime($days . ' days ago'), $limit);

        $this->logger->info('Update old torrents', ['count' => count($torrents)]);
        foreach ($torrents as $torrent) {
            $this->sendDelayed(
                new Message(json_encode(['type' => 'torrent', 'id' => $torrent->getId()->toString()]))
            );
        }

        $shows = $this->showRepository->getOld(new \DateTime($days . ' days ago'), $limit);

        $this->logger->info('Update old shows', ['count' => count($shows)]);
        foreach ($shows as $show) {
            $this->sendDelayed(
                new Message(json_encode(['type' => 'show', 'id' => $show->getId()->toString()]))
            );
        }

        $movies = $this->movieRepository->getOld(new \DateTime($days . ' days ago'), $limit);

        $this->logger->info('Update old movies', ['count' => count($movies)]);
        foreach ($movies as $movie) {
            $this->sendDelayed(
                new Message(json_encode(['type' => 'movie', 'id' => $movie->getId()->toString()]))
            );
        }

        return 0;
    }

    private function sendDelayed(Message $message)
    {
        $message->setDelay(random_int(120, 3600));
        $this->producer->sendEvent(
            SyncProcessor::TOPIC,
            $message
        );
    }
}

