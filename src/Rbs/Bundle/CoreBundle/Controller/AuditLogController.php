<?php

namespace Rbs\Bundle\CoreBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Audit Log controller.
 *
 * @Route("/audit-log")
 */
class AuditLogController extends BaseController
{

    /**
     *
     * @Route("", name="audit_log")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $datatable = $this->get('rbs_erp.core.datatable.audit_log');
        $datatable->buildDatatable();

        return array(
            'datatable' => $datatable,
        );
    }

    /**
     *
     * @Route("/audit_log_list_ajax", name="audit_log_list_ajax", options={"expose"=true})
     * @Method("GET")
     */
    public function listAjaxAction()
    {
        $datatable = $this->get('rbs_erp.core.datatable.audit_log');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {
            $qb->orderBy("audit_log.eventTime", "DESC");
        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }
}
