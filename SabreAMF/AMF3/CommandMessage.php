<?php

    /**
     * SabreAMF_AMF3_CommandMessage 
     * 
     * @uses SabreAMF
     * @uses _AMF3_AbstractMessage
     * @package 
     * @version $Id: CommandMessage.php 233 2009-06-27 23:10:34Z evertpot $
     * @copyright Copyright (C) 2006-2009 Rooftop Solutions. All rights reserved.
     * @author Evert Pot (http://www.rooftopsolutions.nl/) 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */

    require_once 'SabreAMF/AMF3/AbstractMessage.php';

    /**
     * This class is used for service commands, like pinging the server
     */
    class SabreAMF_AMF3_CommandMessage extends SabreAMF_AMF3_AbstractMessage {

        const SUBSCRIBE_OPERATION          = 0;
        const UNSUSBSCRIBE_OPERATION       = 1;
        const POLL_OPERATION               = 2;
        const CLIENT_SYNC_OPERATION        = 4;
        const CLIENT_PING_OPERATION        = 5;
        const CLUSTER_REQUEST_OPERATION    = 7; 
        const LOGIN_OPERATION              = 8;
        const LOGOUT_OPERATION             = 9;
        const SESSION_INVALIDATE_OPERATION = 10;
        const MULTI_SUBSCRIBE_OPERATION    = 11;
        const DISCONNECT_OPERATION         = 12;

        /**
         * operation 
         * 
         * @var int 
         */
        public $operation;

        /**
         * messageRefType 
         * 
         * @var int 
         */
        public $messageRefType;

        /**
         * correlationId 
         * 
         * @var string 
         */
        public $correlationId;

    }


