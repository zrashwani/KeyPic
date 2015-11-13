<?php

namespace Zrashwani\KeyPic;

use Psr\Http\Message\ServerRequestInterface;

/**
 * KeyPic
 *
 * This class will provide a wrapper on keypic webservice for anti-spam
 * compliant with PSR-7 requests
 *
 * @package Keypic
 * @author  Zeid Rashwani <http://zrashwani.com>
 * @version 0.0.1
 */

class KeyPic
{

    /*@var RequestInterface $request */
    private $request;
        
    /**
     * keypic API version number
     * @var string
     */
    private $version;
    
    /**
     * keypic API user agent text
     * @var string
     */
    private $UserAgent;
    
    /**
     * keypic webservice host
     * @var string
     */
    private $host;
    
    /**
     * keypic formID, unique for each client
     * @var string
     */
    private $FormID;
    
    /**
     * keypic publisher ID
     * @var string
     */
    private $PublisherID;
    
    /**
     * Token generated from keypic API
     * @var string
     */
    private $Token;
    
    /**
     * debug flag
     * @var boolean
     */
    private $Debug;
    
    /**
     * name of token field hidden input
     * @var string
     */
    private $TokenInputName;


    /**
     * constructor with injecting psr-7 request in the object
     * @param ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request, $vesion = '2.1')
    {
        $this->request   = $request;
        $this->version   = $vesion;
        $this->UserAgent = 'Keypic PHP Class, Version: '.$this->version;
        $this->host      = 'ws.keypic.com';
    }

    /**
     * set API host value
     * @param string $host
     * @return \Zrashwani\KeyPic\KeyPic
     */
    public function setHost($host)
    {
        $new = clone $this;
        $new->host = $host;
        return $new;
    }

    /**
     * set keypic api version
     * @param string $version
     * @return \Zrashwani\KeyPic\KeyPic
     */
    public function setVersion($version)
    {
        $new = clone $this;
        $new->version = $version;
        return $new;
    }

    /**
     * set user agent
     * @param string $UserAgent
     * @return \Zrashwani\KeyPic\KeyPic
     */
    public function setUserAgent($UserAgent)
    {
        $new = clone $this;
        $new->UserAgent = $UserAgent;
        return $new;
    }

    /**
     * set keypic formId value
     * @param string $FormID
     * @return \Zrashwani\KeyPic\KeyPic
     */
    public function setFormID($FormID)
    {
        $new = clone $this;
        $new->FormID = $FormID;
        return $new;
    }

    /**
     * set publisher Id value
     * @param string $PublisherID
     * @return \Zrashwani\KeyPic\KeyPic
     */
    public function setPublisherID($PublisherID)
    {
        $new = clone $this;
        $new->PublisherID = $PublisherID;
        return $new;
    }

    /**
     * set Debug value
     * @param boolean $Debug
     * @return \Zrashwani\KeyPic\KeyPic
     */
    public function setDebug($Debug)
    {
        $new = clone $this;
        $new->Debug = $Debug;
        return $new;
    }
    
    /**
     * get token hidden input name
     * @return string
     */
    public function getTokenInputName()
    {
        return $this->TokenInputName;
    }
    
    /**
     * set token input hidden field name
     * @param string $tokenInputName
     * @return \Zrashwani\KeyPic\KeyPic
     */
    public function setTokenInputName($tokenInputName)
    {
        $new = clone $this;
        $new->TokenInputName = $tokenInputName;
        return $new;
    }

    /**
     * check keypic formID if valid
     * @param String $FormID
     * @return boolean
     */
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
     * requesting new token or getting its value if it is already generated
     * @param String $Token
     * @param String $ClientEmailAddress
     * @param String $ClientUsername
     * @param String $ClientMessage
     * @param String $ClientFingerprint
     * @param int $Quantity
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
            } elseif ($response['status'] == 'error') {
                throw new \Exception("Keypic error generation, ".$response['error']);
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
                        $this->Token . '" target="_blank"><img src="//' . $this->host
                        . '/?RequestType=getImage&amp;Token=' . $this->Token
                        . '&amp;WidthHeight=' . $WidthHeight . '&amp;Debug='.$this->Debug
                        . '&amp;PublisherID=' . $this->PublisherID
                        . '" alt="Form protected by Keypic" /></a>';
                    break;

                default:
                    $ret =  '<script type="text/javascript" src="//' . $this->host .
                        '/?RequestType=getScript&amp;Token=' . $this->Token
                        . '&amp;WidthHeight=' . $WidthHeight . '&amp;Debug='.$this->Debug
                        . '&amp;PublisherID=' . $this->PublisherID . '"></script>';
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
     * render necessary keypic input and script to use in html form
     * @param string $RequestType
     * @param string $WidthHeight
     * @return String
     */
    public function renderHtml($RequestType = 'getScript', $WidthHeight = '336x280')
    {
        return $this->getTokenInput().$this->getIt($RequestType, $WidthHeight);
    }

    /**
     * Detect if entry is Spam? from 0% to 100%
     * @param string $ClientEmailAddress
     * @param string $ClientUsername
     * @param string $ClientMessage
     * @param string $ClientFingerprint
     * @throw \Exception
     * @return array
     *
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
                throw new \Exception("Error validating keypic token, error: ".$response['error']);
            }
        } else {
            throw new \Exception("Empty keypic token or Form ID");
        }
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
            return $response;
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
        $client = new \GuzzleHttp\Client(['timeout'=>3]);
        $response = $client->post(
            "http://".$this->host,
            ['headers' => ['content-type' => 'application/x-www-form-urlencoded',
                            'User-Agent'   => $this->UserAgent],
              'body'  => $fields,
              'version' => 1.0
              ]
        );
        
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
            $fields['ServerName'] = $serverParams['SERVER_NAME'];
            $fields['ClientIP'] = $serverParams['REMOTE_ADDR'];
            $fields['ClientUserAgent'] = $this->request->getHeader('user-agent')[0];
            $fields['ClientAccept'] = $this->request->getHeader('accept')[0];
            $fields['ClientAcceptEncoding'] = $this->request->getHeader('accept-encoding')[0];
            $fields['ClientAcceptLanguage'] = $this->request->getHeader('accept-language')[0];
            $fields['ClientAcceptCharset'] = $this->request->getHeader('accept-charset')[0];
            $fields['ClientHttpReferer'] = $this->request->getHeader('referer')[0];
            $fields['ClientUsername'] = $ClientUsername;
            $fields['ClientEmailAddress'] = $ClientEmailAddress;
            $fields['ClientMessage'] = $ClientMessage;
            $fields['ClientFingerprint'] = $ClientFingerprint;
            
            return $fields;
    }
}
