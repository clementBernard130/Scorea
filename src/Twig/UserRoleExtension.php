<?php

namespace App\Twig;

use App\Entity\Users;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class UserRoleExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('user_role_color', [$this, 'getUserRoleColor']),
        ];
    }

    /**
     * Get the color for a user based on their highest priority role.
     * This filter delegates to the User entity's getRoleColor() method.
     */
    public function getUserRoleColor(Users $user): string
    {
        return $user->getRoleColor();
    }
}
