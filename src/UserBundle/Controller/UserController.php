<?php

namespace UserBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SosthenG\EntityPortationBundle\Export;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use UserBundle\Form\Type\UserDeleteFormType;
use UserBundle\Form\Type\UserEditFormType;
use UserBundle\Form\Type\UserFormType;

/**
 * User controller.
 *
 * @Route("admin/user")
 * @Security("has_role('ROLE_SUPER_ADMIN')")
 */
class UserController extends Controller
{
    /**
     * Lists all user entities.
     *
     * @Route("/", name="admin_user_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $users = $this->get('fos_user.user_manager')->findUsers();

        return $this->render('@User/User/list.html.twig', array(
            'users' => $users,
        ));
    }

    /**
     * Creates a new user entity.
     *
     * @Route("/new", name="admin_user_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $userManager = $this->get('fos_user.user_manager');

        $user = $userManager->createUser();
        $user->setEnabled(true);

        $form = $this->createForm(UserFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $userManager->updateUser($user);

                $this->addFlash('success', "L'utilisateur ".$user->getUsername()." a bien été ajouté.");

                return $this->redirectToRoute('admin_user_index');
            }
            else {
                $this->addFlash('error', "Merci de corriger les erreurs du formulaire avant de continuer.");
            }
        }

        return $this->render('@User/User/new.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing user entity.
     *
     * @Route("/{id}/edit", name="admin_user_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, User $user)
    {
        if ($user->hasRole('ROLE_ADMIN') && !$this->isGranted("ROLE_SUPER_ADMIN") && $user != $this->getUser()) {
            throw new AccessDeniedHttpException("Vous n'avez pas l'autorisation d'éditer cet utilisateur.");
        }

        $deleteForm = $this->createDeleteForm($user);
        $editForm = $this->createForm(UserEditFormType::class, $user);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted()) {
            if ($editForm->isValid()) {
                if ($user->getPlainPassword() == '') $user->setPlainPassword(null);
                $userManager = $this->get('fos_user.user_manager');

                $userManager->updateUser($user);

                $this->addFlash('success', "L'utilisateur " . $user->getUsername() . " a bien été modifié.");

                // Si l'utilisateur a modifié ses propres droits, on le déconnecte
                /** @var $currentUser User */
                $currentUser = $this->getUser();
                if ($user == $currentUser && $currentUser->getRoles() != $user->getRoles()) {
                    $this->get('security.token_storage')->setToken(null);
                    $this->get('session')->invalidate();
                    return $this->redirectToRoute('fos_user_security_logout');
                }

                return $this->redirectToRoute('admin_user_index');
            }
            else {
                $this->addFlash('error', "Merci de corriger les erreurs du formulaire avant de continuer.");
            }
        }

        return $this->render('@User/User/edit.html.twig', array(
            'user' => $user,
            'form' => $editForm->createView(),
            'confirm_message' => "Voulez-vous vraiment supprimer l'utilisateur ".$user->getUsername()." ?",
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a user entity.
     *
     * @Route("/{id}/delete", name="admin_user_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, User $user)
    {
        if ($user->hasRole('ROLE_ADMIN') && !$this->isGranted("ROLE_SUPER_ADMIN") && $user != $this->getUser()) {
            throw new AccessDeniedHttpException("Vous n'avez pas l'autorisation de supprimer cet utilisateur.");
        }

        $form = $this->createDeleteForm($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->get('fos_user.user_manager')->deleteUser($user);
        }

        return $this->redirectToRoute('admin_user_index');
    }

    /**
     * Creates a simple form with an icon for deleting from a table
     *
     * @param User $user
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteFormAction(User $user) {
        $form = $this->createDeleteForm($user);

        return $this->render('@App/elements/delete_icon.html.twig',
                             array(
                                 'confirm_message' => "Voulez-vous vraiment supprimer l'utilisateur ".$user->getUsername()." ?",
                                 'delete_form' => $form->createView(),
                             )
        );
    }

    /**
     * @Route("/export/{format}", name="admin_user_export", requirements={"format" = "PDF|Excel2007|Excel5|CSV|HTML|XML|OpenDocument"})
     * @Method("GET")
     */
    public function exportAction($format) {
        $users = $this->get('fos_user.user_manager')->findUsers();

        if ($format == 'PDF') {
            \PHPExcel_Settings::setPdfRenderer(\PHPExcel_Settings::PDF_RENDERER_MPDF, $this->get('kernel')->getRootDir()."/../vendor/mpdf/mpdf");
        }

        $exporter = new Export($this->get("phpexcel"), $this->get("translator"));

        $exporter->setEntities($users, true);

        $nomExport = "Export users au ".date('Y-m-d');
        $exporter->setSheetTitle($nomExport);

        return $exporter->getResponse($format, $nomExport);
    }

    /**
     * Creates a form to delete a user entity.
     *
     * @param User $user The user entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(User $user)
    {
        return $this->createForm(UserDeleteFormType::class, null, array('action_url' => $this->generateUrl('admin_user_delete', array('id' => $user->getId()))));
    }
}
