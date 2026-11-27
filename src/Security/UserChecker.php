<?php

namespace App\Security;

use App\Entity\Organization;
use App\Entity\User;
use Override;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{

    public function checkPostAuth(UserInterface $user): void
    {
        //No implemented
    }

    public function checkPreAuth(UserInterface $user): void
    {
        $success = true;
        if ($user instanceof User && $user->isActive() === true) {
            $organization = $user->getOrganization();
            if(($organization === null && in_array(User::ROLE_SUPER_ADMIN, $user->getRoles(), true))
                || ($organization instanceof Organization && $organization->isActive() === false)) {
                $success = false;
            }
        } else {
                $success = false;
        }
        if ($success === false) {
            throw new CustomUserMessageAccountStatusException('User is not authorized');
        }
    }
}
