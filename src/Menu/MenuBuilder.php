<?php
/**
 * MenuBuilder.php
 *
 * PHP Version 8.3+
 *
 * @author David Ghyse <david.ghyse@webcraftdg.fr>
 * @version XXX
 * @package App\Menu
 */

namespace App\Menu;

use Symfony\Bundle\SecurityBundle\Security;

class MenuBuilder
{
    public function __construct(
        private readonly Security $security
    ) {}

    public function build(): array
    {
        $items = [];
        if ($this->security->isGranted('IS_AUTHENTICATED')) {
            $items[] = [
                'label' => 'Séances',
                'route' => 'bo_session_index',
                'activePattern' => 'bo_session',
            ];
            $items[] = [
                'label' => 'Blocs',
                'route' => 'bo_block_index',
                'activePattern' => 'bo_bloc',
            ];
            $items[] =  [
                'label' => 'Activités',
                'route' => 'bo_activity_index',
                'activePattern' => 'bo_activity_',
            ];
            $items[] =  [
                'label' => 'Exercices',
                'route' => 'bo_exercise_index',
                'activePattern' => 'bo_exercise_',
            ];
            $items[] = [
                'label' => 'Mon&nbsp;compte',
                'route' => 'bo_account',
                'activePattern' => 'bo_account',
            ];
        }

        return $items;
    }
}
