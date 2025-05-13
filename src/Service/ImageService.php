<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ImageService
{
    private string $defaultImagePath;

    public function __construct(ParameterBagInterface $params)
    {
        $this->defaultImagePath = $params->get('kernel.project_dir') . '/assets/img/event/groupe-personnes.avif';
    }

    public function getImageSrc(?string $photo): string
    {
        if (empty($photo)) {
            if (!file_exists($this->defaultImagePath)) {
                throw new \RuntimeException('Image par défaut non trouvée');
            }
            return base64_encode(file_get_contents($this->defaultImagePath));
        }
        return $photo;
    }
}
