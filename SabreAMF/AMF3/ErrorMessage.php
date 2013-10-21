<?php

    /**
     * SabreAMF_AMF3_ErrorMessage 
     * 
     * @uses SabreAMF_AMF3_ErrorMessage
     * @package SabreAMF
     * @subpackage AMF3
     * @version $Id: ErrorMessage.php 233 2009-06-27 23:10:34Z evertpot $
     * @copyright Copyright (C) 2006-2009 Rooftop Solutions. All rights reserved.
     * @author Evert Pot (http://www.rooftopsolutions.nl/) 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */

    /**
     * This message is based on Abstract Message
     */
    require_once 'SabreAMF/AMF3/AcknowledgeMessage.php';

    /**
     * This is the receipt for Error Messages 
     */
    class SabreAMF_AMF3_ErrorMessage extends SabreAMF_AMF3_AcknowledgeMessage {

        /**
         * Extended data that the remote destination has chosen to associate with 
         * this error to facilitate custom error processing on the client. 
         * 
         * @var object 
         */
        public $extendedData = null;


        /**
         * The fault code for the error. 
         * 
         * @var string
         */
        public $faultCode = '';


        /**
         * Detailed description of what caused the error. 
         * 
         * @var string
         */
        public $faultDetail = '';


        /**
         * A simple description of the error. 
         *
         * @var string
         */
        public $faultString = '';


        /**
         * Should a root cause exist for the error, this property contains those details.
         *
         * @var object 
         */
        public $rootCause = null;

    }


