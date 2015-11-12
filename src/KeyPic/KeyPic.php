<?php

namespace Zrashwani\KeyPic;

use Psr\Http\Message\ServerRequestInterface;
use GuzzleHttp\Psr7;

class KeyPic {

    /*@var RequestInterface $request */
    private $request;
        
    private $version = '2.1';
    private $UserAgent = 'Keypic PHP Class, Version: 2.1';
    private $SpamPercentage = 70;
    private $host = 'http://ws.keypic.com';
    private $url = '/';
    private $FormID;
    private $PublisherID;
    private $Token;
    private $Debug;

    private function __clone() {
    }

    public function __construct(ServerRequestInterface $request) {
        $this->request = $request;
    }

    public function getHost() {
        return $this->host;
    }

    public function setHost($host) {
        $this->host = $host;
    }

    public function getSpamPercentage() {
        return $this->SpamPercentage;
    }

    public function setSpamPercentage($SpamPercentage) {
        $this->SpamPercentage = $SpamPercentage;
    }

    public function getVersion() {
        return $this->version;
    }

    public function setVersion($version) {
        $this->version = $version;
    }

    public function setUserAgent($UserAgent) {
        $this->UserAgent = $UserAgent;
    }

    public function setFormID($FormID) {
        $this->FormID = $FormID;
    }

    public function getFormID() {
        return $this->FormID;
    }

    public function setPublisherID($PublisherID) {
        $this->PublisherID = $PublisherID;
    }

    public function setDebug($Debug) {
        $this->Debug = $Debug;
    }

    public function checkFormID($FormID) {
        $fields['RequestType'] = 'checkFormID';
        $fields['ResponseType'] = '2';
        $fields['FormID'] = $FormID;

        $response = $this->sendRequest($fields);
        if (empty($response) !== false) {
            return $response;
        }

        return false;
    }

    public function getToken($Token, $ClientEmailAddress = '', $ClientUsername = '', 
                                    $ClientMessage = '', $ClientFingerprint = '', $Quantity = 1) {
        if ($Token) {
            $this->Token = $Token;
            return $this->Token;
        } else {
            $serverParams = $this->request->getServerParams();
            
            $fields['FormID'] = $this->FormID;
            $fields['RequestType'] = 'RequestNewToken';
            $fields['ResponseType'] = '2';
            $fields['Quantity'] = $Quantity;
            $fields['ServerName'] = $serverParams['server_name'];
            $fields['ClientIP'] = $serverParams['remote_client'];
            $fields['ClientUserAgent'] = $this->request->getHeader('user_agent');
            $fields['ClientAccept'] = $this->request->getHeader('accept');
            $fields['ClientAcceptEncoding'] = $this->request->getHeader('accept_encoding');
            $fields['ClientAcceptLanguage'] = $this->request->getHeader('accept_language');
            $fields['ClientAcceptCharset'] = $this->request->getHeader('accept_charset');
            $fields['ClientHttpReferer'] = $this->request->getHeader('referer');
            $fields['ClientUsername'] = $ClientUsername;
            $fields['ClientEmailAddress'] = $ClientEmailAddress;
            $fields['ClientMessage'] = $ClientMessage;
            $fields['ClientFingerprint'] = $ClientFingerprint;

            $response = $this->sendRequest($fields);

            if ($response['status'] == 'new_token') {
                $this->Token = $response['Token'];
                return $response['Token'];
            }
        }

        $this->Token = false;
        return false;
    }

    public function getTokenInput() {
        if ($this->Token) {
            return '<input type="hidden" name="Token" value="' . $this->Token . '" />';
        }

        return false;
    }

    public function getIt($RequestType = 'getScript', $WidthHeight = '336x280') {
        $ret = "";
        if ($this->Token) {
            switch ($RequestType) {
                case 'getImage':
                    $ret =  '<a href="http://' . $this->host . '/?RequestType=getClick&amp;Token=' . 
                        $this->Token . '" target="_blank"><img src="//' . $this->host . 
                        '/?RequestType=getImage&amp;Token=' . $this->Token . '&amp;WidthHeight=' . 
                        $WidthHeight . '&amp;PublisherID=' . $this->PublisherID . '" alt="Form protected by Keypic" /></a>';
                    break;

                default:
                    $ret =  '<script type="text/javascript" src="//' . $this->host . 
                        '/?RequestType=getScript&amp;Token=' . $this->Token . '&amp;WidthHeight=' 
                        . $WidthHeight . '&amp;PublisherID=' . $this->PublisherID . '"></script>';
                    break;
            }
        }

        if(empty($ret) === true){
            $ret = '<a href="http://keypic.com" target="_blank">'.
                    'At the moment Keypic is not properly configured in your system, '.
                    'please check if everything is configured correctly Subscribe to the service or check your FormID</a>';
        }
        
        return $ret;
    }

    // Is Spam? from 0% to 100%
    public function isSpam($Token, $ClientEmailAddress = '', $ClientUsername = '', $ClientMessage = '', $ClientFingerprint = '') {
        $this->Token = $Token;

        if (($this->Token) && ($this->FormID)) {
            $fields['Token'] = $this->Token;
            $fields['FormID'] = $this->FormID;
            $fields['RequestType'] = 'RequestValidation';
            $fields['ResponseType'] = '2';
            $fields['ServerName'] = $this->request->getUri()->getHost();
            $fields['ClientIP'] = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
            $fields['ClientUserAgent'] = $this->request->getHeader('user_agent');
            $fields['ClientAccept'] = $this->request->getHeader('accept');
            $fields['ClientAcceptEncoding'] = $this->request->getHeader('accept_encoding');
            $fields['ClientAcceptLanguage'] = $this->request->getHeader('accept_language');
            $fields['ClientAcceptCharset'] = $this->request->getHeader('accept_charset');
            $fields['ClientHttpReferer'] = $this->request->getHeader('referer');
            $fields['ClientUsername'] = $ClientUsername;
            $fields['ClientEmailAddress'] = $ClientEmailAddress;
            $fields['ClientMessage'] = $ClientMessage;
            $fields['ClientFingerprint'] = $ClientFingerprint;

            $response = $this->sendRequest($fields);

            if ($response['status'] == 'response') {
                return $response['spam'];
            } else if ($response['status'] == 'error') {
                return $response['error'];
            }
        }

        return false;
    }

    public function reportSpam($Token) {
        if ($Token == '') {
            return 'error';
        }
        if ($this->FormID == '') {
            return 'error';
        }

        $fields['Token'] = $Token;
        $fields['FormID'] = $this->FormID;
        $fields['RequestType'] = 'ReportSpam';
        $fields['ResponseType'] = '2';

        $response = $this->sendRequest($fields);
        if (empty($response) !== true) {
            return $response;
        } else {
            return false;
        }
    }

    private function sendRequest($fields) {
        
        $keypic_request = new Psr7\Request("POST", $this->host, 
              ['content-type'=>'application/x-www-form-urlencoded',
               'User-Agent' => $this->UserAgent],
                http_build_query($fields), '1.0');
        
        $client = new \GuzzleHttp\Client(['timeout'=>3]);
        $response = $client->send($keypic_request);
        $result = $response->getBody()->getContents();
        
        if (empty($result) !== true) {
            return json_decode($result, true);
        }

        return false;
    }

}
