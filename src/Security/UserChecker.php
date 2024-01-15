<?php
namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;



class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user)
    {
        // dd(!$user->isActive());
        
        if (!$user instanceof User) {
            return;
        }
        
        if (!$user->isActive() == true) {
            throw new CustomUserMessageAuthenticationException(
                'Votre compte n\'est pas actif'
            );
            
            // $this->addFlash('error', "votre compte n'est pas actif. regardez votre boite mail ou contacter un admin" );
            // return $this->redirectToRoute('app_logout');
        }
        
    }
    public function checkPostAuth(UserInterface $user)
    {
        $this->checkPreAuth($user);
    }







}