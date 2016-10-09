<?php

namespace Rbs\Bundle\CoreBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\Delivery;
use Rbs\Bundle\SalesBundle\Entity\Sms;
use Rbs\Bundle\SalesBundle\Entity\Vehicle;
use Rbs\Bundle\SalesBundle\Helper\SmsParse;
use Rbs\Bundle\UserBundle\Entity\User;
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
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createAction(Request $request)
    {
        $smsApiKey = "79b8428a0dea686430a7f20ccbe857bd";

        if ('POST' === $request->getMethod()) {
            if ($smsApiKey == $request->headers->get('X-API-KEY')) {
                $msisdn = $request->request->get('msisdn');
                $timestamp = $request->request->get('timestamp') ? $request->request->get('timestamp') : date('Y-m-d H:i:s');
                $message = $request->request->get('message');
                $messageid = $request->request->get('messageid') ? $request->request->get('messageid') : 0;

                if ($msisdn == null and $message == null) {
                    $response = new Response(json_encode(array("message" => 'Bad request. Invalid Parameter')), 400);
                } else {
                    try {
                        $entity = new Sms();
                        $entity->setMsg($message);
                        $entity->setDate(new \DateTime($timestamp));
                        $entity->setMobileNo($msisdn);
                        $entity->setSl($messageid);
                        $entity->setStatus('NEW');
                        $this->getDoctrine()->getManager()->persist($entity);
                        $this->getDoctrine()->getManager()->flush();

                        $smsParse = new SmsParse($this->getDoctrine()->getManager());
                        $smsParse->parse($entity);

                        $response = new Response(json_encode(array("message" => 'SMS received Successfully')), 200);
                    } catch (\Exception $e) {
                        $response = new Response(json_encode(array("message" => 'Server Internal Error')), 500);
                    }
                }
            } else {
                $response = new Response(json_encode(array("message" => 'Authentication Fail')), 401);
            }
        } else {
            $response = new Response(json_encode(array("message" => 'Invalid Request')), 404);
        }

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/api/sms_vehicle_receive", name="api_sms_vehicle_receive")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function vehicleCreateAction(Request $request)
    {
        $smsApiKey = "79b8428a0dea686430a7f20ccbe857bd";

        if ('POST' === $request->getMethod()) {

            if ($smsApiKey == $request->headers->get('X-API-KEY')) {

                $senderPhoneNumber = $request->request->get('senderPhoneNumber');
                $timestamp = $request->request->get('timestamp') ? $request->request->get('timestamp') : date('Y-m-d H:i:s');

                $orderId = $request->request->get('order') ? $request->request->get('order') : null;
                $depoId = $request->request->get('depo') ? $request->request->get('depo') : null;

                $driverName = $request->request->get('driverName');
                $driverPhone = $request->request->get('driverPhone');
                $vehicleNumber = $request->request->get('vehicleNumber');

                $remarks = $request->request->get('remarks');
                $message = $request->request->get('message');

                $user = $this->getDoctrine()->getRepository('RbsUserBundle:User')->findByPhoneNumber($senderPhoneNumber);

                if ($driverName == null and $driverPhone == null and $vehicleNumber == null and ($orderId == null or $depoId == null)) {
                    $response = new Response(json_encode(array("message" => 'Bad request. Invalid Parameter')), 400);
                } else {
                    try {
                        $vehicle = new Vehicle();
                        $vehicle->setRemark($remarks);
                        $vehicle->setSmsText($message);
                        if($user->getUserType() == User::AGENT){
                            $order = $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->find($orderId);
                            $vehicle->setAgent($order->getAgent());
                            $vehicle->setTransportGiven(Vehicle::AGENT);
                            $vehicle->setDepo($order->getDepo());
                            $vehicle->setOrderText($order->getId());

                            $delivery = new Delivery();
                            $delivery->addOrder($order);
                            $delivery->setShipped(false);
                            $delivery->setDepo($order->getDepo());
                            $delivery->setTransportGiven(Delivery::AGENT);
                            $vehicle->setDeliveries($delivery);
                            $this->getDoctrine()->getManager()->persist($delivery);
                            $this->getDoctrine()->getManager()->flush();
                        }else{
                            $vehicle->setDepo($this->getDoctrine()->getRepository('RbsCoreBundle:Depo')->find($depoId));
                            $vehicle->setTransportGiven(Vehicle::NOURISH);
                        }
                        $vehicle->setShipped(false);

                        $this->getDoctrine()->getManager()->persist($vehicle);
                        $this->getDoctrine()->getManager()->flush();

                        $response = new Response(json_encode(array("message" => 'SMS received Successfully')), 200);
                    } catch (\Exception $e) {
                        $response = new Response(json_encode(array("message" => 'Server Internal Error')), 500);
                    }
                }
            } else {
                $response = new Response(json_encode(array("message" => 'Authentication Fail')), 401);
            }
        } else {
            $response = new Response(json_encode(array("message" => 'Invalid Request')), 404);
        }

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
