<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Groupe;
use AppBundle\Entity\Individu;
use AppBundle\Repository\GroupeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SosthenG\EntityPortationBundle\Export;
use SosthenG\EntityPortationBundle\Import;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\File;

/**
 * Individu controller.
 *
 * @Route("individu")
 * @Security("has_role('ROLE_USER')")
 */
class IndividuController extends Controller
{
    /**
     * Lists all individu entities.
     *
     * @Route("/list/{page}", name="individu_index", requirements={"page": "\d+"})
     * @Method("GET")
     */
    public function indexAction($page = 1)
    {
        $notFoundMessage = "La page ".$page." n'existe pas.";
        $form_supprime = $this->createDeleteFormMultiple();

        if ($page < 1)
            throw $this->createNotFoundException($notFoundMessage);

        $em = $this->getDoctrine()->getManager();

        $nbParPage = 10; // Ne pas modifier, risque de prendre trop de mémoire

        $individus = $em->getRepository('AppBundle:Individu')->search($page, $nbParPage);

        if (count($individus) == 0) $nbPages = 1;
        else $nbPages = ceil(count($individus)/$nbParPage);

        if ($page > $nbPages)
            throw $this->createNotFoundException($notFoundMessage);

        $indexDebut = $nbParPage*($page-1)+1;
        $indexFin = $indexDebut + $nbParPage - 1;

        return $this->render('@App/individu/index.html.twig', array(
            'individus' => $individus,
            'form_supprime_selection' => $form_supprime->createView(),
            'nbPages' => $nbPages,
            'nbParPage' => $nbParPage,
            'page' => $page,
            'total' => count($individus)
        ));
    }

    /**
     * Return the list of individus sorted for the table and matching the request
     *
     * @Route("/find", name="individu_find")
     * @Method("GET")
     */
    public function findAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $associationChamps = array('', 'i.identifiant', 's.libelle', 'i.nom', 'i.prenom', 'i.email');

        $page = $request->get('page', 0) + 1;
        $recherche = $request->get('search', '');
        $trisRequete = $request->get('col', array());
        $tris = array();


        foreach ($trisRequete as $idColonne => $ordre) {
            if (array_key_exists($idColonne, $associationChamps)) {
                $tris[$associationChamps[$idColonne]] = ($ordre == 1) ? 'DESC' : 'ASC';
            }
        }

        $nbParPages = 10; // Ne pas modifier, risque de prendre trop de mémoire

        /** @var Individu[] $individus */
        $individus = $em->getRepository('AppBundle:Individu')->search($page, $nbParPages, $recherche, $tris);


        $retour = array();
        $retour['total_rows'] = count($individus);
        $retour['rows'] = array();
        foreach ($individus as $individu) {
            $row = array();
            $row[] = $this->renderView("@App/individu/checkbox.html.twig", array('individu' => $individu));
            $row[] = $individu->getIdentifiant();
            $row[] = $individu->getStatut()->getLibelle();
            $row[] = $individu->getNom();
            $row[] = $individu->getPrenom();
            $row[] = $individu->getEmail();
            $row[] = $this->renderView("@App/individu/table_actions.html.twig", array('individu' => $individu));
            $retour['rows'][] = $row;
        }

        return $this->json($retour);
    }

    /**
     * Creates a new individu entity.
     *
     * @Route("/creation", name="individu_new")
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $individu = new Individu();
        $form = $this->createForm('AppBundle\Form\Type\IndividuType', $individu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($individu);
            $em->flush();

            return $this->redirectToRoute('individu_index', array('id' => $individu->getId()));
        }

        return $this->render('@App/individu/new.html.twig', array(
            'individu' => $individu,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and show an individu
     *
     * @Route("/{id}", name="individu_show", condition="request.isXmlHttpRequest()", requirements={"id": "\d+"})
     * @Method("GET")
     */
    public function showAction(Individu $individu)
    {
        $deleteForm = $this->createDeleteForm($individu);
        return $this->render("@App/individu/show.html.twig", array(
            'individu' => $individu,
            'delete_form' => $deleteForm->createView(),
            'confirm_message' => $this->getConfirmMessage($individu->getPrenomNom())
        ));
    }

    /**
     * Displays a form to edit an existing individu entity.
     *
     * @Route("/{id}/edition", name="individu_edit", requirements={"id": "\d+"})
     * @Method({"GET", "POST"})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, Individu $individu)
    {
        $deleteForm = $this->createDeleteForm($individu);
        $editForm = $this->createForm('AppBundle\Form\Type\IndividuType', $individu);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('individu_index', array('id' => $individu->getId()));
        }

        return $this->render('@App/individu/edit.html.twig', array(
            'individu' => $individu,
            'form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'confirm_message' => $this->getConfirmMessage($individu->getPrenomNom()),
        ));
    }

    /**
     * Deletes a individu entity.
     *
     * @Route("/{id}/delete", name="individu_delete", requirements={"id": "\d+"})
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, Individu $individu)
    {
        $form = $this->createDeleteForm($individu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($individu);
            $em->flush();
        }

        return $this->redirectToRoute('individu_index');
    }

    /**
     * Deletes multiple individus
     *
     * @Route("/delete_multiple", name="individu_delete_multiple", condition="request.isXmlHttpRequest()")
     * @Method("DELETE")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteMultipleAction(Request $request)
    {
        $form = $this->createDeleteFormMultiple();
        $form->handleRequest($request);

        if ($form->isSubmitted() && !empty($request->get('individus'))) {
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                foreach ($request->get('individus') as $individuId) {
                    $individu = $em->getRepository('AppBundle:Individu')->find((int)$individuId);
                    if (!empty($individu)) $em->remove($individu);
                }
                $em->flush();

                return new JsonResponse();
            }
            else {
                return new JsonResponse($this->getErrorMessages($form), 400);
            }
        }

        return $this->createAccessDeniedException("Accès interdit.");
    }

    /**
     * Creates a form to delete a individu entity.
     *
     * @param Individu $individu The individu entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Individu $individu)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('individu_delete', array('id' => $individu->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     * Creates a simple form with an icon for deleting from a table
     *
     * @param Individu $individu
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteFormAction(Individu $individu) {
        $form = $this->createDeleteForm($individu);

        return $this->render('@App/elements/delete_icon.html.twig',
                             array(
                                 'confirm_message' => $this->getConfirmMessage($individu->getPrenomNom()),
                                 'delete_form' => $form->createView(),
                             )
        );
    }

    /**
     * @Route("/export/{format}", name="individu_export", requirements={"format" = "PDF|Excel2007|Excel5|CSV|HTML|OpenDocument"})
     * @Method("GET")
     */
    public function exportAction($format) {
        $individus = $this->getDoctrine()->getManager()->getRepository("AppBundle:Individu")->findAll();

        if ($format == 'PDF') {
            \PHPExcel_Settings::setPdfRenderer(\PHPExcel_Settings::PDF_RENDERER_MPDF, $this->get('kernel')->getRootDir()."/../vendor/mpdf/mpdf");
        }

        $exporter = new Export($this->get("phpexcel"), $this->get("translator"));

        $exporter->setEntities($individus, true);

        $nomExport = "Export individus au ".date('Y-m-d');
        $exporter->setSheetTitle($nomExport);

        return $exporter->getResponse($format, $nomExport);
    }

    /**
     * @Route("/import/confirm", name="individu_import_confirm")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function importValidAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $submitForm = $this->createImportSubmitForm();
        $submitForm->handleRequest($request);

        if ($submitForm->isSubmitted()) {
            if ($submitForm->isValid()) {
                $lines = $request->get('lines');
                $groupes = $submitForm->getData()['groupes'];

                foreach ($lines as $line) {
                    $individu = $this->getEntityFromLine(json_decode($line, true));
                    /** @var Groupe $groupe */
                    foreach ($groupes as $groupe) {
                        if ($this->getUser()->hasAccess($groupe))
                            $individu->addGroupe($groupe);
                    }
                    $em->persist($individu);
                }

                $em->flush();

                $this->addFlash('success', 'Les individus ont bien été ajoutés.');

                return new JsonResponse();
            }
            return new JsonResponse($this->getErrorMessages($submitForm), 400);
        }

        return $this->createAccessDeniedException("Accès interdit en dehors du formulaire d'import");
    }

    /**
     * @param array $attributs
     *
     * @return Individu
     */
    private function getEntityFromLine($attributs)
    {
        $em = $this->getDoctrine()->getManager();
        $individu = new Individu();

        foreach ($attributs as $key => $value) {
            if ($key == 'statut') {
                $value = $em->getRepository('AppBundle:Statut')->findOneBy(array('libelle' => $value));
            }
            elseif ($key == 'dateNaissance') {
                $value = new \DateTime($value);
            }
            $setter = 'set'.ucfirst($key);
            $individu->$setter($value);
        }

        return $individu;
    }

    /**
     * @return Form
     */
    private function createImportSubmitForm()
    {
        $user = $this->getUser();
        return $this->createFormBuilder(array())
                           ->setAction($this->generateUrl('individu_import_confirm'))
                           ->add('groupes', EntityType::class, array(
                               'class' => 'AppBundle\Entity\Groupe',
                               'choice_label' => 'nom',
                               'label' => 'Ajouter aux groupes',
                               'multiple' => true,
                               'required' => false,
                               'query_builder' => function(GroupeRepository $er) use ($user) {
                                   return $er->qbFindAllAccessibles($user);
                               },
                           ))
                           ->getForm();
    }

    /**
     * @return Form
     */
    private function createDeleteFormMultiple()
    {
        return $this->createFormBuilder(array())
                    ->setAction($this->generateUrl('individu_delete_multiple'))
                    ->setMethod('DELETE')
                    ->getForm();
    }

    /**
     * @Route("/import", name="individu_import")
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function importAction(Request $request)
    {
        $mimesTypes = array('text/csv', '.csv', 'text/plain', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/xml', 'text/xml', 'application/vnd.oasis.opendocument.spreadsheet', 'text/html');

        /** @var Form $importForm */
        $importForm = $this->createFormBuilder(array())
                     ->setAction($this->generateUrl('individu_import'))
                     ->add('file', FileType::class, array(
                         'constraints' => new File(array('mimeTypes' => $mimesTypes, 'mimeTypesMessage' => 'Ce type de fichier n\'est pas autorisé.'))
                     ))
                     ->getForm();

        $submitForm = $this->createImportSubmitForm();

        $importForm->handleRequest($request);

        if ($importForm->isSubmitted()) {
            if ($importForm->isValid()) {
                $formData = $importForm->getData();

                /** @var UploadedFile $fileTmp */
                $fileTmp = $formData['file'];
                $fileName = md5(uniqid()).'.'.$fileTmp->getClientOriginalExtension();

                $importer = new Import($this->get('phpexcel'), $this->get('translator'));

                $file = $fileTmp->move($this->get('kernel')->getRootDir().'/../var/temp/', $fileName);

                $entities = $importer->createEntitesFromFile($file->getPathname(), Individu::class);

                $lines = array();
                $linePos = 1;
                foreach ($entities as $entity) {
                    $lines[] = $this->getLineFromEntity($entity, ++$linePos, $importer->getNotFoundValues());
                }

                // Supprime le fichier temporaire
                $fs = new Filesystem();
                $fs->remove($file->getPathname());

                return $this->render('@App/individu/import/table.html.twig', array('lines' => $lines));
            }
            else {
                return new JsonResponse($this->getErrorMessages($importForm), 400);
            }
        }

        return $this->render('@App/individu/import/import.html.twig', array(
            'importForm' => $importForm->createView(),
            'submitForm' => $submitForm->createView(),
            'mimesTypes' => implode(',', $mimesTypes)
        ));
    }

    /**
     * @Route("/import/check_line", name="individu_import_check_line", condition="request.isXmlHttpRequest()")
     * @Method("GET")
     */
    public function checkLineAction(Request $request)
    {
        if (empty($request->get('data'))) return new JsonResponse(null, 400);

        $icons = array();
        $em = $this->getDoctrine()->getManager();
        $line = $request->get('data');
        $duplicate = null;
        $typeError = '';
        $wrongStatus = false;

        // Vérifie s'il y a des champs vides
        foreach ($line as $key => $value) {
            $trimed = trim($value);
            if (trim($value) == '') {
                $icons[$key] = $this->renderView('@App/individu/import/not_found.html.twig');
                $typeError = 'danger';
            }
            $line[$key] = $trimed;
        }

        // Vérifie que le statut est correct
        if (!empty($line['statut'])) {
            $statut = $em->getRepository('AppBundle:Statut')->findOneBy(array('libelle' => $line['statut']));
            if ($statut === null) {
                $wrongStatus = true;
            }
        }

        // Vérifie si il y a un doublon
        if (!empty($line['identifiant']) && !empty($line['statut']) && !$wrongStatus) {
            $duplicate = $em->getRepository('AppBundle:Individu')->findOneBy(array('identifiant' => $line['identifiant'], 'statut' => $statut));
            if ($duplicate !== null) {
                $icons['identifiant'] = $this->renderView('@App/individu/import/duplicate.html.twig', array('duplicate' => $duplicate, 'champ' => 'identifiant'));
                $typeError = 'danger';
            }
        }
        // Vérifie s'il y a une similarité dans les données
        if ($duplicate === null) {
            $similar = $em->getRepository('AppBundle:Individu')->findSimilarity($line['prenom'], $line['nom'], $line['email'], $line['telephone']);
            if ($similar !== null) {
                if ($similar->getNom() == $line['nom'] && $similar->getPrenom() == $line['prenom']) {
                    $icons['prenom'] = $this->renderView('@App/individu/import/similar.html.twig', array('similar' => $similar, 'champ' => 'prenom'));
                    $icons['nom'] = $this->renderView('@App/individu/import/similar.html.twig', array('similar' => $similar, 'champ' => 'nom'));
                }
                if ($similar->getEmail() == $line['email']) {
                    $icons['email'] = $this->renderView('@App/individu/import/similar.html.twig', array('similar' => $similar, 'champ' => 'email'));
                }
                if ($similar->getTelephone() == $line['telephone']) {
                    $icons['telephone'] = $this->renderView('@App/individu/import/similar.html.twig', array('similar' => $similar, 'champ' => 'telephone'));
                }
                if (empty($typeError)) $typeError = 'warning';
            }
        }

        return new JsonResponse(array('typeError' => $typeError, 'wrongStatus' => $wrongStatus, 'icons' => $icons));
    }

    /**
     * @param Individu $entity
     * @param int      $linePos
     * @param array    $notFoundValues
     *
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function getLineFromEntity(Individu $entity, $linePos, array $notFoundValues = array())
    {
        $em = $this->getDoctrine()->getManager();
        $values = array();

        $error = false;
        $duplicate = $similar = null;
        $tabOptions = array(
            'value' => '',
            'notFound' => false,
            'duplicate' => false,
            'similar' => false
        );

        $line = array(
            'identifiant' => $tabOptions,
            'statut' => $tabOptions,
            'nom' => $tabOptions,
            'prenom' => $tabOptions,
            'dateNaissance' => $tabOptions,
            'email' => $tabOptions,
            'telephone' => $tabOptions,
        );

        foreach ($line as $key => $valueArray) {
            $method = 'get'.ucfirst($key);
            // Get the value from the entity method
            if (is_callable(array($entity, $method)) && !empty($entity->$method())) {
                $line[$key]['value'] = $entity->$method();
            }
            // Get the value from the "not found values" array
            elseif (!empty($notFoundValues[$linePos][$key])) {
                $line[$key]['value'] = $notFoundValues[$linePos][$key];
            }
            // Statut is a special case
            elseif ($key == 'statut' && !empty($notFoundValues[$linePos]['StatutLibelle'])) {
                $statut = $em->getRepository('AppBundle:Statut')->findOneBy(array('libelle' => $notFoundValues[$linePos]['StatutLibelle']));
                if ($statut !== null)
                    $line[$key]['value'] = $statut;
            }
            // Else, value is not found.
            else {
                $line[$key]['notFound'] = true;
                $error = true;
            }
        }

        // Vérifie si il y a un doublon
        if (!empty($line['identifiant']['value']) && !empty($line['statut']['value'])) {
            $duplicate = $em->getRepository('AppBundle:Individu')->findOneBy(array('identifiant' => $line['identifiant'], 'statut' => $line['statut']));
            if ($duplicate !== null) {
                $line['identifiant']['duplicate'] = true;
            }
        }
        // Vérifie s'il y a une similarité dans les données
        if ($duplicate === null) {
            $similar = $em->getRepository('AppBundle:Individu')->findSimilarity($line['prenom']['value'], $line['nom']['value'], $line['email']['value'], $line['telephone']['value']);
            if ($similar !== null) {
                if ($similar->getNom() == $line['nom']['value'] && $similar->getPrenom() == $line['prenom']['value']) {
                    $line['nom']['similar'] = true;
                    $line['prenom']['similar'] = true;
                }
                if ($similar->getEmail() == $line['email']['value']) {
                    $line['email']['similar'] = true;
                }
                if ($similar->getTelephone() == $line['telephone']['value']) {
                    $line['telephone']['similar'] = true;
                }
            }
        }

        $line['options'] = array(
            'error' => $error,
            'similar' => $similar,
            'duplicate' => $duplicate
        );

        return $line;
    }

    private function getErrorMessages(Form $form)
    {
        $errors = array();

        // Global
        foreach ($form->getErrors() as $error) {
            $errors[$form->getName()][] = $error->getMessage();
        }

        // Fields
        foreach ($form as $child) {
            if (!$child->isValid()) {
                foreach ($child->getErrors() as $error) {
                    $errors[$child->getName()][] = $error->getMessage();
                }
            }
        }

        return $errors;
    }

    /**
     * @param $prenomNom
     *
     * @return string
     */
    private function getConfirmMessage($prenomNom) {
        return "Voulez-vous vraiment supprimer l'individu ".$prenomNom." ?";
    }
}
