<?php

namespace App\Controller;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Service\SendEmail;
use App\Form\RegistrationFormType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request,
     UserPasswordEncoderInterface $passwordEncoder,
     TokenGeneratorInterface $tokenGenerator,
     SendEmail $sendEmail
     ): Response
    {
        $user = new User();
      
        $form = $this->createForm(RegistrationFormType::class, $user);
        
        $form->handleRequest($request);
       
        if ($form->isSubmitted() && $form->isValid()) {
           $registrationToken=$tokenGenerator->generateToken();
        
            $user
            ->setRegistrationToken($registrationToken)
            ->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
           // $registrationToken=$tokenGenerator->generateToken();
       
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            
            $entityManager->flush();
            $sendEmail->send([
                'recipient_email'=>$user->getEmail(),
                'subject'=>"verification de votre adresse email pour activer votre compte utilisateur",
                'html_template'=>"registration/register_confirmation_email.html.twig",
                'context'=>[
                    'userID'=>$user->getId(),
                    'registrationToken'=>$registrationToken,
                    'tokenLifeTime'=>$user->getAccountMustBeVerifiedBefore()->format('d/m/Y à H:i')

                ]
            ]);
           
            $this->addFlash('success',"Votre compte utilisateur a bien été crée ,veuillez vous rendre sur votre compte pour l'activer");
           // $this->addFlash('success', 'Article Created! Knowledge is power!');
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
            
        ]);
    }
    /**
     * @Route("/{id}<\d+>/{token}", name="app_verify_account" ,methods={"GET"})
     */
    public function verifyUserAccount(Request $request,
   EntityManagerInterface $EntityManager,
     User $user,
     string $token
     ): Response
    {
        if (($user->getRegistrationToken() === null) 
        || ($user->getRegistrationToken() != $token
        || ($this->isNotRequestedInTime($user->getAccountMustBeVerifiedBefore()))))
        {
            throw new AccessDeniedException();
        }
        $user->setIsVerified(true);
        $user->setAccountVerifiedAt(new \DateTimeImmutable('now'));
        $user->setRegistrationToken(null);
        $EntityManager->flush();
        $this->addFlash('success',"Votre compte utilisateur a bien été activer,vous pouvez vous connecter");
         return $this->redirectToRoute('app_login');
    }
    private function isNotRequestedInTime(\DateTimeImmutable $accountMustBeVerifiedBefore):bool
        {
            return (new \DateTimeImmutable('now')>$accountMustBeVerifiedBefore);
        }
}
