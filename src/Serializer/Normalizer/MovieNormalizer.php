<?php

namespace App\Serializer\Normalizer;

use App\Entity\Movie;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class MovieNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface, NormalizerAwareInterface
{
    private $normalizer;

    public function __construct(ObjectNormalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public function setNormalizer(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    public function normalize($object, $format = null, array $context = array()): array
    {
        if (!$object instanceof Movie) {
            return [];
        }
        $torrents = [];
        foreach ($object->getTorrents() as $torrent) {
            $torrents[$torrent->getLanguage()][$torrent->getQuality()] = $this->normalizer->normalize($torrent, $format, $context);
        }

        return [
            '_id' => $object->getImdb(),
            'imdb_id' => $object->getImdb(),
            'title' => $object->getTitle(),
            'year' => $object->getYear(),
            'synopsis' => $object->getSynopsis(),
            'runtime' => $object->getRuntime(),
            'released' => $object->getReleased(),
            'certification' => $object->getCertification(),
            'torrents' => $torrents,
            'trailer' => $object->getTrailer(),
            'genres' => $object->getGenres(),
            'images' => $object->getImages()->getApiArray(),
            'rating' => $object->getRating()->getApiArray(),
        ];
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof Movie;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}