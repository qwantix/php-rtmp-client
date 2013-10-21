<?php

    /**
     * SabreAMF_AMF3_AbstractMessage 
     * 
     * @package 
     * @version $Id: AbstractMessage.php 233 2009-06-27 23:10:34Z evertpot $
     * @copyright Copyright (C) 2006-2009 Rooftop Solutions. All rights reserved.
     * @author Evert Pot (http://www.rooftopsolutions.nl/) 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */
    abstract class SabreAMF_AMF3_AbstractMessage {

        /**
         * The body of the message 
         * 
         * @var mixed
         */
        public $body;
        
        /**
         * Unique client ID 
         * 
         * @var string 
         */
        public $clientId;
       
        /**
         * destination 
         * 
         * @var string 
         */
        public $destination;
      
        /**
         * Message headers 
         * 
         * @var array 
         */
        public $headers;
      
        /**
         * Unique message ID 
         * 
         * @var string 
         */
        public $messageId;
        
        /**
         * timeToLive 
         * 
         * @var int 
         */
        public $timeToLive;

        /**
         * timestamp 
         * 
         * @var int 
         */
        public $timestamp;

        public function generateRandomId() {

            $SabreAMFID = '44445501';

            $id = md5(microtime());

            return $SabreAMFID . '-' . substr($id,0,4) . '-' . substr($id,4,4) . '-' . substr($id,8,12);

        }

    }


