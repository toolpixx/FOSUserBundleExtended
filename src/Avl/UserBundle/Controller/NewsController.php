<?php

namespace Avl\UserBundle\Controller;

use Avl\UserBundle\Entity\News;
use Avl\UserBundle\Form\Type\NewsType;
use Avl\UserBundle\Form\Type\SubUserSearchFormType;
use Symfony\Component\HttpFoundation\Request;
use Avl\UserBundle\Controller\Controller as BaseController;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * News controller.
 *
 */
class NewsController extends BaseController
{

    /**
     * Lists all News entities.
     *
     */
    public function indexAction(Request $request)
    {
        $form = $this->createForm(new SubUserSearchFormType());
        $form->submit($request);

        $query = $this->getDoctrine()
            ->getManager()
            ->getRepository('UserBundle:News')
            ->findAllNewsByQuery(
                $form->getData()
            );

        $entities = $this->get('knp_paginator')
            ->paginate(
                $query,
                $request->query->get('page', 1),
                5
            );

        return $this->render('UserBundle:News:index.html.twig', array(
            'entities' => $entities,
            'form' => $form->createView()
        ));
    }

    /**
     * Creates a new News entity.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $session = new Session();
        $entity = new News($this->getUser());

        $form = $form = $this->createForm(new NewsType(), $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $session->getFlashBag()->add('notice', 'news.flash.create.success');
            return $this->redirect($this->generateUrl('avl_news', array('newsId' => $entity->getId())));
        }

        return $this->render('UserBundle:News:edit.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a News entity.
     *
     */
    public function showAction($newsId)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('UserBundle:News')->find($newsId);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find News entity.');
        }

        $deleteForm = $this->createDeleteForm($newsId);

        return $this->render('UserBundle:News:show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing News entity.
     */
    public function editAction(Request $request, $newsId)
    {
        $session = new Session();
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('UserBundle:News')->find($newsId);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find News entity.');
        }

        $form = $this->createForm(new NewsType(), $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->flush();

            $session->getFlashBag()->add('notice', 'news.flash.edit.success');
            return $this->redirect($this->generateUrl('avl_news', array('newsId' => $newsId)));
        }

        return $this->render('UserBundle:News:edit.html.twig', array(
            'entity' => $entity,
            'form' => $form->createView()
        ));
    }

    /**
     * Deletes a News entity.
     *
     */
    public function deleteAction(Request $request, $newsId)
    {
        $session = new Session();

        if ($request->getMethod() == 'DELETE') {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('UserBundle:News')->find($newsId);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find News entity.');
            }

            $em->remove($entity);
            $em->flush();
            $session->getFlashBag()->add('notice', 'news.flash.remove.success');
        }

        return $this->redirect($this->generateUrl('avl_news'));
    }
}
