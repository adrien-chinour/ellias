<?php

namespace App\Controller;

use App\Entity\Email;
use App\Form\EmailType;
use App\Repository\EmailRepository;
use App\Service\AliasApiInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/email")
 */
final class EmailController extends AbstractController
{

    /** @var \App\Service\AliasApiInterface $api */
    private $api;

    /** @var \App\Repository\EmailRepository $repository */
    private $repository;

    /**
     * @param \App\Service\AliasApiInterface $api
     * @param EmailRepository                $repository
     */
    public function __construct(AliasApiInterface $api, EmailRepository $repository)
    {
        $this->api = $api;
        $this->repository = $repository;
    }

    /**
     * @Route("/", name="email_index", methods={"GET"})
     * @return Response
     */
    public function index(): Response
    {
        return $this->render(
            'email/index.html.twig',
            ['emails' => $this->repository->findAll()]
        );
    }

    /**
     * @Route("/new", name="email_new", methods={"GET","POST"})
     * @param Request $request
     *
     * @return Response
     */
    public function new(Request $request): Response
    {
        $email = new Email();
        $email->setAlias(uniqid() . "@chinour.fr");

        $form = $this->createForm(EmailType::class, $email);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->save($email);
            $this->api->addAlias($email->getTarget(), $email->getAlias());

            return $this->redirectToRoute('email_index');
        }

        return $this->render(
            'email/new.html.twig',
            ['email' => $email, 'form' => $form->createView()]
        );
    }

    /**
     * @Route("/{id}/edit", name="email_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Email   $email
     *
     * @return Response
     */
    public function edit(Request $request, Email $email): Response
    {
        $form = $this->createForm(EmailType::class, $email, ['edition' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->repository->save($email);
            return $this->redirectToRoute('email_index');
        }

        return $this->render(
            'email/edit.html.twig',
            ['email' => $email, 'form' => $form->createView()]
        );
    }

    /**
     * @Route("/{id}", name="email_delete", methods={"DELETE"})
     * @param Request $request
     * @param Email   $email
     *
     * @return Response
     */
    public function delete(Request $request, Email $email): Response
    {
        if ($this->isCsrfTokenValid('delete' . $email->getId(),
            $request->request->get('_token'))) {
            $this->api->deleteAlias($email->getTarget(), $email->getAlias());
            $this->repository->delete($email);
        }

        return $this->redirectToRoute('email_index');
    }
}
