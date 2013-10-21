<?php

    /**
     * SabreAMF_AMF3_Wrapper 
     * 
     * @package SabreAMF
     * @subpackage AMF3
     * @version $Id: Wrapper.php 233 2009-06-27 23:10:34Z evertpot $
     * @copyright Copyright (C) 2006-2009 Rooftop Solutions. All rights reserved.
     * @author Evert Pot (http://www.rooftopsolutions.nl/) 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     */
    class SabreAMF_AMF3_Wrapper {


        /**
         * data 
         * 
         * @var mixed
         */
        private $data;


        /**
         * __construct 
         * 
         * @param mixed $data 
         * @return void
         */
        public function __construct($data) {

            $this->setData($data);

        }
        

        /**
         * getData 
         * 
         * @return mixed 
         */
        public function getData() {

            return $this->data;

        }

        /**
         * setData 
         * 
         * @param mixed $data 
         * @return void
         */
        public function setData($data) {

            $this->data = $data;

        }
            

    }


