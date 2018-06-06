<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Groupe;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SosthenG\EntityPortationBundle\Export;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Groupe controller.
 *
 * @Route("groupe")
 */
class GroupeController extends Controller
{
    /**
     * Lists all groupe entities.
     *
     * @Route("/", name="groupe_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $groupes = $em->getRepository('AppBundle:Groupe')->findAll(array(), array('nom' => 'ASC'));

        return $this->render('@App/groupe/tableau.html.twig', array(
            'groupes' => $groupes,
        ));
    }

    /**
     * Creates a new groupe entity.
     *
     * @Route("/new", name="groupe_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $groupe = new Groupe();
        $form = $this->createForm('AppBundle\Form\Type\GroupeType', $groupe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $groupe->addUserAutorise($this->getUser());
            $em->persist($groupe);
            $em->flush();

            return $this->redirectToRoute('groupe_index', array('id' => $groupe->getId()));
        }

        return $this->render('@App/groupe/new.html.twig', array(
            'groupe' => $groupe,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a groupe entity.
     *
     * @Route("/{id}", name="groupe_show", condition="request.isXmlHttpRequest()", requirements={"id": "\d+"})
     * @Method("GET")
     */
    public function showAction(Groupe $groupe)
    {
        $deleteForm = $this->createDeleteForm($groupe);

        return $this->render('@App/groupe/show.html.twig', array(
            'groupe' => $groupe,
            'delete_form' => $deleteForm->createView(),
            'confirm_message' => $this->getConfirmMessage($groupe->getNom())
        ));
    }

    /**
     * Displays a form to edit an existing groupe entity.
     *
     * @Route("/{id}/edit", name="groupe_edit", requirements={"id": "\d+"})
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, Groupe $groupe)
    {
        if (!$this->getUser()->hasAccess($groupe))
            throw $this->createAccessDeniedException("Vous n'êtes pas autorisé à modifier ce groupe");

        $deleteForm = $this->createDeleteForm($groupe);
        $editForm = $this->createForm('AppBundle\Form\Type\GroupeType', $groupe);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('groupe_index', array('id' => $groupe->getId()));
        }

        return $this->render('@App/groupe/edit.html.twig', array(
            'groupe' => $groupe,
            'form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'confirm_message' => $this->getConfirmMessage($groupe->getNom())
        ));
    }

    /**
     * Deletes a groupe entity.
     *
     * @Route("/{id}", name="groupe_delete", requirements={"id": "\d+"})
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, Groupe $groupe)
    {
        if (!$this->getUser()->hasAccess($groupe))
            throw $this->createAccessDeniedException("Vous n'êtes pas autorisé à supprimer ce groupe");

        $form = $this->createDeleteForm($groupe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($groupe);
            $em->flush();
        }

        return $this->redirectToRoute('groupe_index');
    }

    /**
     * @Route("{id}/export/{format}", name="groupe_export", requirements={"id": "\d+", "format" = "PDF|Excel2007|Excel5|CSV|HTML|OpenDocument"})
     * @Method("GET")
     */
    public function exportAction(Groupe $groupe, $format) {
        $individus = $groupe->getAllIndividus()->toArray();

        if ($format == 'PDF') {
            \PHPExcel_Settings::setPdfRenderer(\PHPExcel_Settings::PDF_RENDERER_MPDF, $this->get('kernel')->getRootDir()."/../vendor/mpdf/mpdf");
        }

        $exporter = new Export($this->get("phpexcel"), $this->get("translator"));

        $exporter->setEntities($individus, true);

        $nomExport = "Export du groupe ".$groupe->getNom();
        $exporter->setSheetTitle(substr($groupe->getNom(), 0, 31)); // Substr car sheetTitle doit <= 31 char
        $exporter->getProperties()->setTitle($nomExport);

        return $exporter->getResponse($format, $nomExport." ".date('Y-m-d'));
    }

    /**
     * Creates a form to delete a groupe entity.
     *
     * @param Groupe $groupe The groupe entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Groupe $groupe)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('groupe_delete', array('id' => $groupe->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * @param $nomGroupe
     *
     * @return string
     */
    private function getConfirmMessage($nomGroupe) {
        return "Voulez-vous vraiment supprimer le groupe ".$nomGroupe." ?";
    }
}
