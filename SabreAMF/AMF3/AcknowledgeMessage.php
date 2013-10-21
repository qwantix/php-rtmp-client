<?php

    /**
     * SabreAMF_AMF3_AcknowledgeMessage 
     * 
     * @uses SabreAMF_AMF3_AbstractMessage
     * @package SabreAMF
     * @subpackage AMF3
     * @version $Id: AcknowledgeMessage.php 233 2009-06-27 23:10:34Z evertpot $
     * @copyright Copyright (C) 2006-2009 Rooftop Solutions. All rights reserved.
     * @author Evert Pot (http://www.rooftopsolutions.nl/) 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */

    /**
     * This message is based on Abstract Message
     */
    require_once 'SabreAMF/AMF3/AbstractMessage.php';

    /**
     * This is the receipt for any message thats being sent
     */
    class SabreAMF_AMF3_AcknowledgeMessage extends SabreAMF_AMF3_AbstractMessage {

       /**
        * The ID of the message where this is a receipt of 
        * 
        * @var string 
        */
       public $correlationId;

       public function __construct(SabreAMF_AMF3_AbstractMessage $message = null) {

            $this->messageId = $this->generateRandomId();
            $this->clientId = $this->generateRandomId();
            $this->destination = null;
            $this->body = null;
            $this->timeToLive = 0;
            $this->timestamp = time() . '00';
            $this->headers = new STDClass();

            if ($message) {
                $this->correlationId = $message->messageId;
            }

        }

    }


