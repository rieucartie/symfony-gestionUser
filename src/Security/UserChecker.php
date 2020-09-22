<?php
namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user):void
    {
        
        if (!$user instanceof User) {
            dd($user);
        }
         
        if (!$user->getIsVerified()) {
            // the message passed to this exception is meant to be displayed to the user
            throw new CustomUserMessageAccountStatusException("Votre compte n'est pas actif ,
            veuillez consulter vos mails avant le
            {$user->getAccountMustBeVerifiedBefore()->format('d/m/Y à H:i')}
            ");
        }
    }

    public function checkPostAuth(UserInterface $user):void
    {
        if (!$user instanceof User) {
            return;
        }

    }
}