<?php

namespace Rbs\Bundle\CoreBundle\Controller;

use Rbs\Bundle\SalesBundle\Entity\Sms;
use Rbs\Bundle\SalesBundle\Helper\ChickOrderSmsParser;
use Rbs\Bundle\SalesBundle\Helper\PaymentSmsParse;
use Rbs\Bundle\SalesBundle\Helper\SmsParse;
use Rbs\Bundle\SalesBundle\Helper\SmsVehicleParse;
use Rbs\Bundle\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Command;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * ApiController.
 */
class ApiController extends BaseController
{
    /**
     * @Route("/api/sms_receive", name="api_sms_receive")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $smsApiKey = "79b8428a0dea686430a7f20ccbe857bd";

        if ('POST' === $request->getMethod()) {
            if ($smsApiKey == $request->headers->get('X-API-KEY')) {
                $msisdn = $request->request->get('msisdn');
                $timestamp = $request->request->get('timestamp') ? $request->request->get('timestamp') : date('Y-m-d H:i:s');
                $orderVia = $request->request->get('orderVia');
                $message = $request->request->get('message');
                $messageid = $request->request->get('messageid') ? $request->request->get('messageid') : 0;

                if ($msisdn == null and $message == null) {
                    $response = new Response(json_encode(array("message" => 'Bad request. Invalid Parameter')), 400);
                }elseif (!empty($msisdn) && $orderVia=='APP'){

                    $agent = $this->getDoctrine()->getRepository('RbsSalesBundle:Agent')->getAgentByPhone($msisdn);
                    if($agent){
                        try {
                            $entity = new Sms();
                            $entity->setMsg($message);
                            $entity->setDate(new \DateTime($timestamp));
                            $entity->setMobileNo($msisdn);
                            $entity->setSl($messageid);
                            $entity->setStatus('NEW');
                            $entity->setType('FD');
                            $this->getDoctrine()->getManager()->persist($entity);
                            $this->getDoctrine()->getManager()->flush();

                            $smsParse = new SmsParse($this->getDoctrine()->getManager(), $this->container, $msisdn);
                            $return_value= $smsParse->parse($entity);
//                            var_dump($return_value);die;
                            if (array_key_exists("orderId",$return_value))
                            {
                                $paymentError = '';
                                if (array_key_exists('errorMessage',$return_value) && $return_value['errorMessage']!=''){
                                    $paymentError.= 'But payment does not paid. Because '.$return_value['errorMessage'];
                                }
                                $orderId = $return_value['orderId'];

                                $return_value = array('message'=>'Order received Successfully. Your order id '.$orderId.'.'.$paymentError, 'orderId'=>$orderId);
                                $return_value['status']=200;
                            }elseif (array_key_exists('message', $return_value)){
                                $return_value= array('message'=>$return_value['message']);
                                $return_value['status']= 200;
                            }
                            $response = new Response(json_encode($return_value), $return_value['status']);

                        } catch (\Exception $e) {
                            $response = new Response(json_encode(array("message" => 'Server Internal Error')), 500);
                        }
                    }else{
                        $response = new Response(json_encode(array("message" => 'Agent mobile no does not match.')), 401);
                    }
                }else {
                        try {
                            $entity = new Sms();
                            $entity->setMsg($message);
                            $entity->setDate(new \DateTime($timestamp));
                            $entity->setMobileNo($msisdn);
                            $entity->setSl($messageid);
                            $entity->setStatus('NEW');
                            $entity->setType('FD');
                            $this->getDoctrine()->getManager()->persist($entity);
                            $this->getDoctrine()->getManager()->flush();

                            $smsParse = new SmsParse($this->getDoctrine()->getManager(), $this->container, $msisdn);
                            $return_value=$smsParse->parse($entity);
                            if (array_key_exists("orderId",$return_value))
                            {
                                $paymentError = '';
                                if (array_key_exists('errorMessage',$return_value) && $return_value['errorMessage']!=''){
                                    $paymentError.= 'But payment does not paid. Because '.$return_value['errorMessage'];
                                }
//                                var_dump($return_value);
                                $orderId = $return_value['orderId'];

                                $return_value = array('message'=>'Order received Successfully. Your order id '.$orderId.'.'.$paymentError, 'orderId'=>$orderId);
                                $return_value['status']=200;
                            }elseif (array_key_exists('message', $return_value)){
                                $return_value= array('message'=>$return_value['message']);
                                $return_value['status']= 200;
                            }
                            $response = new Response(json_encode($return_value), $return_value['status']);

//                            $response = new Response(json_encode(array("message" => 'SMS received Successfully')), 200);
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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function vehicleCreateAction(Request $request)
    {
        $smsApiKey = "79b8428a0dea686430a7f20ccbe857bd";

        if ('POST' === $request->getMethod()) {

            if ($smsApiKey == $request->headers->get('X-API-KEY')) {

                $senderPhoneNumber = $request->request->get('senderPhoneNumber');
                $message = $request->request->get('message');

                $user = $this->getDoctrine()->getRepository('RbsUserBundle:User')->findByPhoneNumber($senderPhoneNumber);

                if($user == null) {
                    $response = new Response(json_encode(array("message" => 'Invalid Phone Number')), 400);
                }else{
                    if ($message == null) {
                        $response = new Response(json_encode(array("message" => 'Bad request. Invalid Parameter')), 400);
                    } else {
                        try {
                            $smsVehicleParse = new SmsVehicleParse($this->getDoctrine()->getManager(), $user[0]);
                            $return_value =  $smsVehicleParse->parse($message);

                            $response = new Response(json_encode($return_value), $return_value['status']);
                        } catch (\Exception $e) {
                            $response = new Response(json_encode(array("message" => 'Server Internal Error')), 500);
                        }
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
     * @Route("/api/sms_order_chick", name="api_sms_order_chick")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function orderChickAction(Request $request)
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

                    $user = $this->getDoctrine()->getRepository('RbsUserBundle:User')->findByPhoneNumber($msisdn);
                    if ($user == null) {
                        return new Response(json_encode(array("message" => 'Authentication Fail')), 401);
                    }

                    /** @var User $user */
                    $user = $user[0];
                    if ($user->getUserType() != USER::SR) {
                        return new Response(json_encode(array("message" => 'Authentication Fail')), 401);
                    }

                    try {
                        $entity = new Sms();
                        $entity->setMsg($message);
                        $entity->setDate(new \DateTime($timestamp));
                        $entity->setMobileNo($msisdn);
                        $entity->setSl($messageid);
                        $entity->setStatus('NEW');
                        $entity->setType('CK');
                        $this->getDoctrine()->getManager()->persist($entity);
                        $this->getDoctrine()->getManager()->flush();

                        $smsParse = new ChickOrderSmsParser($this->get('doctrine.orm.entity_manager'));
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
     * @Route("/api/sms_payment_receive", name="api_sms_payment_receive")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function paymentReceiveAction(Request $request)
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

                        $smsParse = new PaymentSmsParse($this->getDoctrine()->getManager(), $this->container, $msisdn);
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
     * @Route("/api/products", name="api_product_list")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getProductListAction(Request $request)
    {
        $smsApiKey = "79b8428a0dea686430a7f20ccbe857bd";

        if ('GET' === $request->getMethod()) {
            if ($smsApiKey == $request->headers->get('X-API-KEY')) {
                $items = $this->getDoctrine()->getRepository('RbsCoreBundle:Item')->getAllItems();
                $response= new Response(json_encode($items));

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
     * @Route("/api/itemTypes", name="api_item_type_list")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getItemTypeListAction(Request $request)
    {
        $smsApiKey = "79b8428a0dea686430a7f20ccbe857bd";

        if ('GET' === $request->getMethod()) {
            if ($smsApiKey == $request->headers->get('X-API-KEY')) {
                $itemTypes = $this->getDoctrine()->getRepository('RbsCoreBundle:ItemType')->getActiveItemType();
                $response= new Response(json_encode($itemTypes));

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
     * @Route("/api/payments", name="api_payments_by_date")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getPaymentsByDateAction(Request $request)
    {
        $paymentApiKey = $this->getParameter('payment_api_key');

        $date = $request->query->get('request_date');
        $bank_slug = $request->query->get('bank_slug');
        $receiveAccount = $request->query->get('receive_account');

        if ('GET' === $request->getMethod()) {
            if ($paymentApiKey == $request->headers->get('X-API-KEY')) {
                $payments = $this->getDoctrine()->getRepository('RbsSalesBundle:Payment')->getPaymentsByDate($date, $bank_slug, $receiveAccount);
                $response= new JsonResponse($payments, 200);

            } else {
                $response = new JsonResponse(array("message" => 'Authentication Fail'), 401);
            }
        } else {
            $response = new JsonResponse(array("message" => 'Invalid Request'), 404);
        }

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/api/orders", name="api_orders_by_date")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getOrdersByDateAction(Request $request)
    {
        $orderApiKey = $this->getParameter('order_api_key');

        $date = $request->query->get('request_date');

        if ('GET' === $request->getMethod()) {
            if ($orderApiKey == $request->headers->get('X-API-KEY')) {
                $payments = $this->getDoctrine()->getRepository('RbsSalesBundle:Delivery')->getDeliveryQuantityByZoneWiseForApi($date);
                $response= new JsonResponse($payments, 200);

            } else {
                $response = new JsonResponse(array("message" => 'Authentication Fail'), 401);
            }
        } else {
            $response = new JsonResponse(array("message" => 'Invalid Request'), 404);
        }

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
    /**
     * @Route("/api/order/details", name="api_order_details_by_region")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getOrderDetailsByRegionAction(Request $request)
    {
        $orderApiKey = $this->getParameter('order_api_key');

        $date = $request->query->get('request_date');
        $districtIds = $request->query->get('district_id');

        if ('GET' === $request->getMethod()) {
            if ($orderApiKey == $request->headers->get('X-API-KEY')) {
                $payments = $this->getDoctrine()->getRepository('RbsSalesBundle:Delivery')->getDeliveryQuantityDetailsByDateAndZoneWiseForApi($date, $districtIds);
                $response= new JsonResponse($payments, 200);

            } else {
                $response = new JsonResponse(array("message" => 'Authentication Fail'), 401);
            }
        } else {
            $response = new JsonResponse(array("message" => 'Invalid Request'), 404);
        }

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
