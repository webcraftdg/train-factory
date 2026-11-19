<?php

namespace App\Twig;

use App\Exception\HttpException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AngularAsset extends AbstractExtension
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('angular_asset', [$this, 'getAsset']),
        ];
    }

    public function getAsset(string $name, string $extension): string
    {
        $directory = $this->projectDir . '/public/angular';

        // Recherche des fichiers avec hash (production)
        $files = glob(sprintf(
            '%s/%s-*.%s',
            $directory,
            $name,
            $extension
        ));

        if (!empty($files)) {
            usort($files, fn($a, $b) => filemtime($b) <=> filemtime($a));

            return '/angular/' . basename($files[0]);
        }

        // Recherche sans hash (développement)
        $file = sprintf(
            '%s/%s.%s',
            $directory,
            $name,
            $extension
        );

        if (is_file($file)) {
            return '/angular/' . basename($file);
        }

        throw new HttpException(
            message: sprintf('Asset Angular introuvable : %s.%s', $name, $extension),
            code: 500
        );
    }
}
