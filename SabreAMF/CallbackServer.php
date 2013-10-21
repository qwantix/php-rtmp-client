<?php

    require_once 'SabreAMF/Server.php';
    require_once 'SabreAMF/AMF3/AbstractMessage.php';
    require_once 'SabreAMF/AMF3/AcknowledgeMessage.php';
    require_once 'SabreAMF/AMF3/RemotingMessage.php';
    require_once 'SabreAMF/AMF3/CommandMessage.php';
    require_once 'SabreAMF/AMF3/ErrorMessage.php';
    require_once 'SabreAMF/DetailException.php';

    /**
     * AMF Server
     * 
     * This is the AMF0/AMF3 Server class. Use this class to construct a gateway for clients to connect to 
     *
     * The difference between this server class and the regular server, is that this server is aware of the
     * AMF3 Messaging system, and there is no need to manually construct the AcknowledgeMessage classes.
     * Also, the response to the ping message will be done for you.
     * 
     * @package SabreAMF 
     * @version $Id: CallbackServer.php 233 2009-06-27 23:10:34Z evertpot $
     * @copyright Copyright (C) 2006-2009 Rooftop Solutions. All rights reserved.
     * @author Evert Pot (http://www.rooftopsolutions.nl/) 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause)
     * @uses SabreAMF_Server
     * @uses SabreAMF_Message
     * @uses SabreAMF_Const
     */
    class SabreAMF_CallbackServer extends SabreAMF_Server {

        /**
         * Assign this callback to handle method-calls 
         *
         * @var callback
         */
        public $onInvokeService;

        /**
         * Assign this callback to handle authentication requests 
         * 
         * @var callback 
         */
        public $onAuthenticate;

        /**
         * handleCommandMessage 
         * 
         * @param SabreAMF_AMF3_CommandMessage $request 
         * @return Sabre_AMF3_AbstractMessage 
         */
        private function handleCommandMessage(SabreAMF_AMF3_CommandMessage $request) {

            switch($request->operation) {

                case SabreAMF_AMF3_CommandMessage::CLIENT_PING_OPERATION :
                    $response = new SabreAMF_AMF3_AcknowledgeMessage($request);
                    break;
                case SabreAMF_AMF3_CommandMessage::LOGIN_OPERATION :
                    $authData = base64_decode($request->body);
                    if ($authData) {
                        $authData = explode(':',$authData,2);
                        if (count($authData)==2) {
                            $this->authenticate($authData[0],$authData[1]);
                        }
                    }
                    $response = new SabreAMF_AMF3_AcknowledgeMessage($request);
                    $response->body = true;
                    break;
                case SabreAMF_AMF3_CommandMessage::DISCONNECT_OPERATION :
                    $response = new SabreAMF_AMF3_AcknowledgeMessage($request);
                    break;
                default :
                    throw new Exception('Unsupported CommandMessage operation: '  . $request->operation);

            }
            return $response;

        }

        /**
         * authenticate 
         * 
         * @param string $username 
         * @param string $password 
         * @return void
         */
        protected function authenticate($username,$password) {

            if (is_callable($this->onAuthenticate)) {
                call_user_func($this->onAuthenticate,$username,$password);
            }

        }

        /**
         * invokeService 
         * 
         * @param string $service 
         * @param string $method 
         * @param array $data 
         * @return mixed 
         */
        protected function invokeService($service,$method,$data) {

            if (is_callable($this->onInvokeService)) {
                return call_user_func_array($this->onInvokeService,array($service,$method,$data));
            } else {
                throw new Exception('onInvokeService is not defined or not callable');
            }

        }


        /**
         * exec
         * 
         * @return void
         */
        public function exec() {

            // First we'll be looping through the headers to see if there's anything we reconize

            foreach($this->getRequestHeaders() as $header) {

                switch($header['name']) {

                    // We found a credentials headers, calling the authenticate method
                    case 'Credentials' :
                        $this->authenticate($header['data']['userid'],$header['data']['password']);
                        break;

                }

            }

            foreach($this->getRequests() as $request) {

                // Default AMFVersion
                $AMFVersion = 0;

                $response = null;

                try {

                    if (is_array($request['data']) && isset($request['data'][0]) && $request['data'][0] instanceof SabreAMF_AMF3_AbstractMessage) {
                        $request['data'] = $request['data'][0];
                    }

                    // See if we are dealing with the AMF3 messaging system
                    if (is_object($request['data']) && $request['data'] instanceof SabreAMF_AMF3_AbstractMessage) {
            
                        $AMFVersion = 3;
                       
                        // See if we are dealing with a CommandMessage
                        if ($request['data'] instanceof SabreAMF_AMF3_CommandMessage) {

                            // Handle the command message 
                            $response = $this->handleCommandMessage($request['data']);
                        }

                        // Is this maybe a RemotingMessage ?
                        if ($request['data'] instanceof SabreAMF_AMF3_RemotingMessage) {

                            // Yes
                            $response = new SabreAMF_AMF3_AcknowledgeMessage($request['data']);
                            $response->body = $this->invokeService($request['data']->source,$request['data']->operation,$request['data']->body);

                        }

                    } else {

                        // We are dealing with AMF0
                        $service = substr($request['target'],0,strrpos($request['target'],'.'));
                        $method  = substr(strrchr($request['target'],'.'),1);
                        
                        $response = $this->invokeService($service,$method,$request['data']);

                    }

                    $status = SabreAMF_Const::R_RESULT;

                } catch (Exception $e) {

                    // We got an exception somewhere, ignore anything that has happened and send back
                    // exception information

                    if ($e instanceof SabreAMF_DetailException) {
                        $detail = $e->getDetail();
                    } else {
                        $detail = '';
                    }

                    switch($AMFVersion) {
                        case SabreAMF_Const::AMF0 :
                            $response = array(
                                'description' => $e->getMessage(),
                                'detail'      => $detail,
                                'line'        => $e->getLine(), 
                                'code'        => $e->getCode()?$e->getCode():get_class($e),
                            );
                            break;
                        case SabreAMF_Const::AMF3 :
                            $response = new SabreAMF_AMF3_ErrorMessage($request['data']);
                            $response->faultString = $e->getMessage();
                            $response->faultCode   = $e->getCode();
                            $response->faultDetail = $detail;
                            break;

                    }
                    $status = SabreAMF_Const::R_STATUS;
                }

                $this->setResponse($request['response'],$status,$response);

            }
            $this->sendResponse();

        }

    }


