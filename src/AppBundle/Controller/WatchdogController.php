<?php

namespace AppBundle\Controller;

use Doctrine\DBAL\Connection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use SosthenG\EntityPortationBundle\Export;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * WatchdogController controller.
 *
 * @Route("admin/watchdog")
 * @Security("has_role('ROLE_SUPER_ADMIN')")
 */
class WatchdogController extends Controller
{
    /**
     * @Route("/", name="admin_watch_index")
     */
    public function watchAction()
    {
        $em = $this->getDoctrine()->getManager();

        $logs = $em->getRepository('AppBundle:Logs')->findBy(array(), array('loggedAt' => 'DESC'));

        // TODO : Améliorer avec pagination etc.

        return $this->render('@App/watchdog/index.html.twig', array(
            'logs' => $logs,
            'clear_form' => $this->createClearForm()->createView(),
            'confirm_message' => 'Voulez-vous vraiment vider les logs ?'));
    }

    /**
     * @Route("/export/{format}", name="logs_export", requirements={"format" = "PDF|Excel2007|Excel5|CSV|HTML|OpenDocument"})
     * @Method("GET")
     */
    public function exportAction($format) {
        $logs = $this->getDoctrine()->getManager()->getRepository("AppBundle:Logs")->findAll();

        if ($format == 'PDF') {
            \PHPExcel_Settings::setPdfRenderer(\PHPExcel_Settings::PDF_RENDERER_MPDF, $this->get('kernel')->getRootDir()."/../vendor/mpdf/mpdf");
        }

        $exporter = new Export($this->get("phpexcel"), $this->get("translator"));

        $exporter->setEntities($logs, true);

        $nomExport = "Logs au ".date('Y-m-d');
        $exporter->setSheetTitle($nomExport);

        return $exporter->getResponse($format, $nomExport);
    }

    /**
     * Creates a form to delete the logs
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createClearForm()
    {
        return $this->createFormBuilder()
                    ->setAction($this->generateUrl('logs_clear'))
                    ->setMethod('DELETE')
                    ->getForm()
            ;
    }

    /**
     * @Route("/clear", name="logs_clear")
     * @Method("DELETE")
     */
    public function clearAction(Request $request)
    {
        $form = $this->createClearForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Connection $connection */
            $connection = $this->getDoctrine()->getConnection();
            $platform   = $connection->getDatabasePlatform();
            $connection->executeUpdate($platform->getTruncateTableSQL('GRP_logs'));
            $this->addFlash('success', 'Les logs ont été vidés');
        }
        else {
            $this->addFlash('error', 'Impossible de vider les logs.');
        }
        return $this->redirectToRoute('admin_watch_index');
    }
}


