<?php
namespace App\Controller;

use App\Form\ContactType;
use App\Model\Contact;
use App\Notification\ContactNotification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerTrait;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Mailer;

class ContactController extends AbstractController
{
    /**
     * Contact page display
     * @Route("/contact",name="contact_index",  methods={"GET","POST"})
     * @return Response A reponse instance
     */
    public function contact(Request $request, MailerInterface $mailer): Response
    {
        $contact = new Contact();
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = (new templatedEmail())
                ->from($this->getParameter('mailer_from'))
                ->to(new Address($this->getParameter('mailer_to')))
                ->subject("Vous avez reçu un email d'un visiteur !")
                ->html($this->renderView('contact/mail.html.twig', [
                    'contact' => $contact
                ]));

            $mailer->send($email);

            $this->addFlash('message', 'Votre message a été transmis, nous vous répondrons dans les meilleurs délais.');
        }
        return $this->render('contact/index.html.twig', [
                'form' => $form->createView()]);
    }
}
