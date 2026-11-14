<?php

namespace App\Twig;

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

        $files = glob(sprintf(
            '%s/%s-*.%s',
            $directory,
            $name,
            $extension
        ));

        if (!$files || count($files) !== 1) {
            throw new \RuntimeException(
                sprintf('Asset Angular introuvable ou ambigu : %s.%s', $name, $extension)
            );
        }

        return '/angular/' . basename($files[0]);
    }
}
