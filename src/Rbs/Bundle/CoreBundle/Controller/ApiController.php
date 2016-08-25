<?php

namespace Rbs\Bundle\CoreBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\Sms;
use Rbs\Bundle\SalesBundle\Helper\SmsParse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Command;

/**
 * ApiController.
 */
class ApiController extends BaseController
{
    /**
     * @Route("/api/sms_receive", name="api_sms_receive")
     * @Method("POST")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(Request $request)
    {
        $smsApiKey = "79b8428a0dea686430a7f20ccbe857bd";

        if ('POST' === $request->getMethod()) {
            if($smsApiKey == $request->headers->get('X-API-KEY')){
                $msisdn     = $request->request->get('msisdn');
                $timestamp  = $request->request->get('timestamp');
                $message    = $request->request->get('message');
                $messageid  = $request->request->get('messageid');

                if($msisdn == null and $message == null){
                    $response = new Response(json_encode(array("HTTPCode" => 404)));
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;
                }

                if($message != null and $msisdn != null){
                    $entity = new Sms();
                    $entity->setMsg($message);
                    $entity->setDate($timestamp);
                    $entity->setMobileNo($msisdn);
                    $entity->setSl($messageid);
                    $entity->setStatus('NEW');
                    $this->getDoctrine()->getManager()->persist($entity);
                    $this->getDoctrine()->getManager()->flush();

                    $smsParse = new SmsParse($this->getDoctrine()->getManager());
                    $parse = $smsParse->parse($entity);

                    if($parse == false){
                        $response = new Response(json_encode(array("HTTPCode" => 500)));
                        $response->headers->set('Content-Type', 'application/json');
                        return $response;
                    }
                    $response = new Response(json_encode(array("HTTPCode" => 200)));
                    $response->headers->set('Content-Type', 'application/json');
                    return $response;
                }
            }else{
                $response = new Response(json_encode(array("HTTPCode" => 401)));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
        }
    }
}
