<?php

    /**
     * SabreAMF_AMF3_RemotingMessage 
     * 
     * @uses SabreAMF_AM3_AbstractMessage
     * @package SabreAMF
     * @subpackage AMF3
     * @version $Id: RemotingMessage.php 233 2009-06-27 23:10:34Z evertpot $
     * @copyright Copyright (C) 2006-2009 Rooftop Solutions. All rights reserved.
     * @author Evert Pot (http://www.rooftopsolutions.nl/) 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */

    require_once 'SabreAMF/AMF3/AbstractMessage.php';

    /**
     * Invokes a message on a service
     */
    class SabreAMF_AMF3_RemotingMessage extends SabreAMF_AMF3_AbstractMessage {

        /**
         * operation 
         * 
         * @var string 
         */
        public $operation;

        /**
         * source 
         * 
         * @var string 
         */
        public $source;

        /**
         * Creates the object and generates some values 
         * 
         * @return void
         */
        public function __construct() {

            $this->messageId = $this->generateRandomId();
            $this->clientId = $this->generateRandomId();
            $this->destination = null;
            $this->body = null;
            $this->timeToLive = 0;
            $this->timestamp = time() . '00';
            $this->headers = new STDClass();

        }
    }


