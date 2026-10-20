<?php

namespace App\Twig;

use App\Menu\MenuBuilder;
use Twig\Attribute\AsTwigFunction;

final class MenuExtension
{

    public function __construct(
        private readonly MenuBuilder $menuBuilder
    )
    {
    }

    #[AsTwigFunction('main_menu')]
    public function getMainMenu(): array
    {
        return $this->menuBuilder->build();
    }
}
