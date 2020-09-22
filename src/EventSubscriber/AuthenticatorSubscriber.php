<?php

namespace App\EventSubscriber;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Http\Event\SwitchUserEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\DeauthenticatedEvent;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;	
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthenticatorSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $securityLogger;
    private RequestStack $requestStack;

    public function __construct(LoggerInterface $securityLogger,RequestStack $requestStack)
    {
        $this->securityLogger = $securityLogger;
        $this->requestStack = $requestStack;
    }
    /** @return array<string>   */
    public static function getSubscribedEvents()
    {
        return [
    //'security.authentication.failure' => 'onSecurityAuthenticationFailure',
      //ou le meme differement  
    AuthenticationEvents::AUTHENTICATION_FAILURE => 'onSecurityAuthenticationFailure',
    AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onSecurityAuthenticationSuccess',   
    SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',  
    'Symfony\Component\Security\Http\Event\LogoutEvent'  => 'onSecurityLogout',
    'security.logout_on_change' => 'onSecurityLogoutOnChange', 
    SecurityEvents::SWITCH_USER => 'onSecuritySwitchUser',
];
    }
    public function onSecurityAuthenticationFailure(AuthenticationFailureEvent $event):void
    {
        ['user_IP'  => $userIP ]=$this->getRouteNameandUserIP();
        /**@var tokenInterface $securityToken */
        $securityToken= $event->getAuthenticationToken();
        ['email'  => $emailEntered ]=$securityToken->getCredentials();
        $this->securityLogger->info("un utilisateur à l'adresse IP '{$userIP}' 
        a tenté de s'authentifier avec le mail
       : '{$emailEntered}' :-).");
    }
    public function onSecurityAuthenticationSuccess(AuthenticationSuccessEvent $event):void
    {
        [
            'user_IP'  => $userIP ,
            'route_name'  => $routeName,
        ]=$this->getRouteNameandUserIP();

        if(empty($event->getAuthenticationToken()->getRoleNames())) {
            $this->securityLogger->info("un utilisateur à l'adresse IP '{$userIP}' est apparu sur la route
            : '{$routeName}' :-).");
        }
        else {
            $securityToken=$event->getAuthenticationToken();
            $userEmail=$this->getUserEmail($securityToken);
            $this->securityLogger->info("un utilisateur anonyme à l'adresse IP '{$userIP}' 
             a évolué en entity User avec l'adresse
            : '{$userEmail}' :-).");
        }
    }
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event):void
    {
        ['user_IP'  => $userIP ]=$this->getRouteNameandUserIP();
         /**@var tokenInterface $securityToken */
         $securityToken= $event->getAuthenticationToken();
         $userEmail=$this->getUserEmail($securityToken);
         $this->securityLogger->info("un utilisateur anonyme à l'adresse IP '{$userIP}' 
             a évolué en entity User avec l'adresse
            : '{$userEmail}' :-).");
    }
    public function onSecurityLogout(LogoutEvent $event):void
    {
        /** @var RedirectResponse|null  $response */
        $response= $event->getResponse();
        /** @var tokenInterface|null $securityToken */
        $securityToken= $event->getToken();
        if(!$response|| !$securityToken){
            return;
        }
        ['user_IP'  => $userIP ]=$this->getRouteNameandUserIP();

        $userEmail=$this->getUserEmail($securityToken);
        
        $targetUrl=$response->getTargetUrl();

        $this->securityLogger->info("l'utilisateur à l'adresse IP '{$userIP}' 
        avec l'adresse email
       : '{$userEmail}' s'est deconnecté  et a été redirigé vers : '{$targetUrl}'
        :-).");


    }
    public function onSecurityLogoutOnChange(DeauthenticatedEvent $event):void
    {
        // ...
    }
    public function onSecuritySwitchUser(SwitchUserEvent $event):void
    {
        // ...
    }
    /**
     * return user ip et la route que le user provient
     *@return array{user_IP:string|null,route_name:mixed}
     */
    private function getRouteNameandUserIP():array{
        $request = $this->requestStack->getCurrentRequest();
        if(!$request){
            return[
                'user_IP'  => 'inconnu',
                'route_name'  => 'inconnu',
            ];
        }
        return[
            'user_IP'  => $request->getClientIp() ??  'inconnu',
            'route_name'  => $request->attributes->get('_route'),
        ];
    }
    private function getUserEmail(TokenInterface $securityToken):string{
        /** @var User $user */
        $user = $securityToken->getUser();
        return  $user->getEmail(); 
    }
}
