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
                'route' => 'app_session_index',
                'activePattern' => 'app_session',
            ];
            $items[] = [
                'label' => 'Blocs',
                'route' => 'app_block_index',
                'activePattern' => 'app_bloc',
            ];
            $items[] =  [
                'label' => 'Activités',
                'route' => 'app_activity_index',
                'activePattern' => 'app_activity_',
            ];
            $items[] =  [
                'label' => 'Exercices',
                'route' => 'app_exercise_index',
                'activePattern' => 'app_exercise_',
            ];
            $items[] = [
                'label' => 'Mon&nbsp;compte',
                'route' => 'app_account',
                'activePattern' => 'app_account',
            ];
        }

        return $items;
    }
}
