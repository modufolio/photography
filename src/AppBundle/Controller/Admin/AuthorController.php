<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Entity\Author;
use AppBundle\Form\AuthorType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Author controller.
 *
 * @Route("admin/author")
 */
class AuthorController extends Controller
{
    /**
     * List all authors.
     *
     * @Route("/", name="admin_authors_index")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $authors = $em->getRepository('AppBundle:Author')->findAll();

        return $this->render('admin/author/index.html.twig', [
            'authors' => $authors,
        ]);
    }

    /**
     * Save an new author.
     *
     * @Route("/new", name="admin_author_new")
     */
    public function newAction(Request $request, TranslatorInterface $translator)
    {
        $author = new Author();
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($author);
            $em->flush();

            $url = $this->generateUrl('admin_author_edit', ['id' => $author->getId()]);

            $translated = $translator->trans('success.new');
            $this->addFlash(
                'success',
                $translated.': <a class="alert-link" href="'.$url.'">'.$author->getName().'</a>.'
            );

            return $this->redirectToRoute('admin_author_new');
        }

        return $this->render('admin/author/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Save an existing author.
     *
     * @Route("/edit/{id}", name="admin_author_edit")
     */
    public function editAction(Request $request, TranslatorInterface $translator, Author $author)
    {
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($author);
            $em->flush();

            $translated = $translator->trans('success.edit');
            $this->addFlash(
                'success',
                $translated.'.'
            );
        }

        return $this->render('admin/author/edit.html.twig', [
            'author' => $author,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Delete an author.
     *
     * @Route("/delete/{id}", name="admin_author_delete")
     */
    public function deleteAction(TranslatorInterface $translator, Author $author)
    {
        $em = $this->getDoctrine()->getManager();

        $translated = str_replace('%author%', $author->getName(), $translator->trans('success.deleted.author'));

        $em->remove($author);
        $em->flush();

        $this->addFlash(
            'success',
            $translated.'.'
        );

        return $this->redirectToRoute('admin_index');
    }
}
