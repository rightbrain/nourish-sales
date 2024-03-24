<?php

namespace Rbs\Bundle\SalesBundle\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class SmsGateWay
{

    private $username;
    private $password;
    /**
     * @var Client
     */
    private $client;

    public function  __construct($username, $password, Client $client)
    {
        $this->username = $username;
        $this->password = $password;
        $this->client = $client;
    }

    function send($msg, $phone){
        try {
//            $apiEndPoint = 'https://smpp.ajuratech.com:7790/sendtext';
            $apiEndPoint = 'https://smpp.revesms.com:7790/sendtext';
            $response = $this->client->request('GET', $apiEndPoint, array(
                'query' => array(
                    'apikey' => $this->username,
                    'secretkey' => $this->password,
                    'callerID' => 'NOURISH',
                    'toUser' => $phone,
                    'messageContent' => $msg,
                )
            ));
            if ($response->getStatusCode() == 200)
            {
                print_r($response->getBody()->getContents()) ;
            }

        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                echo 'Caught exception: ',  $e->getMessage();
//                var_dump($e->getResponse()->getReasonPhrase());
            }
        }

        /*$curl = curl_init();
        $data =[
            "username"=>$this->username,
            "password"=>$this->password,
            "sender"=>"NOURISH",
            "message"=>$msg,
            "to"=>$phone
        ];
        curl_setopt_array($curl, array(
            CURLOPT_URL => "http://api.icombd.com/api/v2/sendsms/plaintext",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            echo $response;
        }*/

        /*try {

            $body = '{"authentication": {"username": "' . $this->username .'","password": "'.$this->password.'"},"messages": [{"sender": "Nourish","text": "'.$msg.'","recipients": [{"gsm": "'.$phone.'"}]}]}';

            $response = $this->client->post(
//                "/api/v3/sendsms/json",
                "/api/v1/campaigns/sendsms/plain",
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept'       => '/',
                    ],
                    'body'    => $body,
                ]
            );
            $content  = $response->getBody()->getContents();
//            var_dump($content);
        } catch (RequestException $e) {
//            var_dump($e->getRequest());
            if ($e->hasResponse()) {
//                var_dump($e->getResponse()->getReasonPhrase());
            }
        }*/

    }
}