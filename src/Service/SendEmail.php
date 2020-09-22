<?php 
namespace App\Service;
use Symfony\Bridge\Mime\Address;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
Class SendEmail{
private MailerInterface $mailer;
private string $senderEmail;
private string $senderName;

public function __construct(MailerInterface $mailer,  
string $senderEmail,string $senderName)
    {
        $this->mailer=$mailer;
        $this->senderEmail=$senderEmail;
        $this->senderName=$senderName;
    } 
     /**  
     * @param array<mixed> $arguments
     * 
     */
    public function send(array $arguments):void
    {[
        'recipient_email'=>$recipientEmail,
        'subject'=>$subject,
        'html_template'=>$htmlTemplate,
        'context'=>$context,
        
    ]=$arguments;

    $email = (new TemplatedEmail())
    ->from($this->senderEmail,$this->senderName)
    ->to($recipientEmail)
    ->subject($subject)

    // path of the Twig template to render
    ->htmlTemplate($htmlTemplate)

    // pass variables (name => value) to the template
    ->context($context);

   
try {
    $this->mailer->send($email);
} catch (TransportExceptionInterface $mailerException) {
     throw $mailerException;
}
    }  
    
    

}
