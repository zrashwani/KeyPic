<?php

namespace Zrashwani\KeyPic;

use Psr\Http\Message\ServerRequestInterface;
use GuzzleHttp\Psr7;

class KeyPic
{

    /*@var RequestInterface $request */
    private $request;
        
    private $version = '2.1';
    private $UserAgent = 'Keypic PHP Class, Version: 2.1';
    private $SpamPercentage = 70;
    private $host = 'http://ws.keypic.com';
    private $FormID;
    private $PublisherID;
    private $Token;
    private $Debug;
    private $TokenInputName;


    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function setHost($host)
    {
        $new = clone $this;
        $new->host = $host;
        return $new;
    }

    public function getSpamPercentage()
    {
        return $this->SpamPercentage;
    }

    public function setSpamPercentage($SpamPercentage)
    {
        $new = clone $this;
        $new->SpamPercentage = $SpamPercentage;
        return $new;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion($version)
    {
        $new = clone $this;
        $new->version = $version;
        return $new;
    }

    public function setUserAgent($UserAgent)
    {
        $new = clone $this;
        $new->UserAgent = $UserAgent;
        return $new;
    }

    public function setFormID($FormID)
    {
        $new = clone $this;
        $new->FormID = $FormID;
        return $new;
    }

    public function getFormID()
    {
        return $this->FormID;
    }

    public function setPublisherID($PublisherID)
    {
        $new = clone $this;
        $new->PublisherID = $PublisherID;
        return $new;
    }

    public function setDebug($Debug)
    {
        $new = clone $this;
        $new->Debug = $Debug;
        return $new;
    }
    
    public function getTokenInputName()
    {
        return $this->TokenInputName;
    }
    
    public function setTokenInputName($tokenInputName)
    {
        $new = clone $this;
        $new->TokenInputName = $tokenInputName;
        return $new;
    }

    public function checkFormID($FormID)
    {
        $fields['RequestType'] = 'checkFormID';
        $fields['ResponseType'] = '2';
        $fields['FormID'] = $FormID;

        $response = $this->sendRequest($fields);
        if (empty($response) !== false) {
            return $response;
        }

        return false;
    }

    /**
     *
     * @param type $Token
     * @param type $ClientEmailAddress
     * @param type $ClientUsername
     * @param type $ClientMessage
     * @param type $ClientFingerprint
     * @param type $Quantity
     * @return boolean
     */
    public function getToken(
        $Token,
        $ClientEmailAddress = '',
        $ClientUsername = '',
        $ClientMessage = '',
        $ClientFingerprint = '',
        $Quantity = 1
    ) {
        if ($Token) {
            $this->Token = $Token;
            return $this->Token;
        } else {
            $fields = $this->getWebServiceFields(
                "RequestNewToken",
                $ClientEmailAddress,
                $ClientUsername,
                $ClientFingerprint,
                $ClientMessage,
                $Quantity
            );
            $response = $this->sendRequest($fields);

            if ($response['status'] == 'new_token') {
                $this->Token = $response['Token'];
                return $response['Token'];
            }
        }

        $this->Token = false;
        return false;
    }

    /**
     * get keypic token hidden field text
     * @return boolean|string
     */
    public function getTokenInput()
    {
        if ($this->Token) {
            return '<input type="hidden" name="'.$this->TokenInputName.'" value="' . $this->Token . '" />';
        }
        return false;
    }

    /**
     * print keypic token into form as pixel image or script
     * @param string $RequestType
     * @param string $WidthHeight
     * @return string
     */
    public function getIt($RequestType = 'getScript', $WidthHeight = '336x280')
    {
        $ret = "";
        if ($this->Token) {
            switch ($RequestType) {
                case 'getImage':
                    $ret =  '<a href="http://' . $this->host . '/?RequestType=getClick&amp;Token=' .
                        $this->Token . '" target="_blank"><img src="//' . $this->host .
                        '/?RequestType=getImage&amp;Token=' . $this->Token . '&amp;WidthHeight=' .
                        $WidthHeight . '&amp;PublisherID=' . $this->PublisherID .
                        '" alt="Form protected by Keypic" /></a>';
                    break;

                default:
                    $ret =  '<script type="text/javascript" src="//' . $this->host .
                        '/?RequestType=getScript&amp;Token=' . $this->Token . '&amp;WidthHeight='
                        . $WidthHeight . '&amp;PublisherID=' . $this->PublisherID . '"></script>';
                    break;
            }
        }

        if (empty($ret) === true) {
            $ret = '<a href="http://keypic.com" target="_blank">'.
                    'Incorrect keypic configuration</a>';
        }
        
        return $ret;
    }

    /**
     * Detect if entry is Spam? from 0% to 100%
     * @param string $ClientEmailAddress
     * @param string $ClientUsername
     * @param string $ClientMessage
     * @param string $ClientFingerprint
     * @return array
     *
     * @todo amend to return array
     */
    public function isSpam(
        $ClientEmailAddress = '',
        $ClientUsername = '',
        $ClientMessage = '',
        $ClientFingerprint = ''
    ) {
        $this->Token = $this->request->getParsedBody()[$this->TokenInputName];

        if (($this->Token) && ($this->FormID)) {
            $fields = $this->getWebServiceFields(
                "RequestValidation",
                $ClientUsername,
                $ClientEmailAddress,
                $ClientMessage,
                $ClientFingerprint
            );
            $fields['Token'] = $this->Token;

            $response = $this->sendRequest($fields);

            if ($response['status'] == 'response') {
                return $response['spam'];
            } elseif ($response['status'] == 'error') {
                return $response['error'];
            }
        }

        return false;
    }

    /**
     * sending reportSpam method to keypic webservice
     * @return boolean|array
     */
    public function reportSpam()
    {
        $Token = $this->request->getParsedBody()[$this->TokenInputName];
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
            return json_decode($response);
        } else {
            return false;
        }
    }

    /**
     * sending spam detection request to keypic servers
     * @param  array          $fields
     * @return boolean|array
     */
    private function sendRequest(array $fields)
    {
        
        $keypic_request = new Psr7\Request(
            "POST",
            $this->host,
            ['content-type'=>'application/x-www-form-urlencoded',
               'User-Agent' => $this->UserAgent],
            http_build_query($fields),
            '1.0'
        );
        
        $client = new \GuzzleHttp\Client(['timeout'=>3]);
        $response = $client->send($keypic_request);
        $result = $response->getBody()->getContents();
        
        if (empty($result) !== true) {
            return json_decode($result, true);
        }

        return false;
    }
    
    /**
     * getting fields used in webservice for requesting and validating keypic token
     * @param String $RequestType
     * @param String $ClientUsername
     * @param String $ClientEmailAddress
     * @param String $ClientMessage
     * @param String $ClientFingerprint
     * @param int $Quantity
     * @return array
     */
    protected function getWebServiceFields(
        $RequestType,
        $ClientUsername = "",
        $ClientEmailAddress = "",
        $ClientMessage = "",
        $ClientFingerprint = "",
        $Quantity = 1
    ) {
        
            $serverParams = $this->request->getServerParams();
            $fields = [];
            
            $fields['FormID'] = $this->FormID;
            $fields['RequestType'] = $RequestType;
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
            
            return $fields;
    }
}
