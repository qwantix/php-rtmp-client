<?php

    require_once dirname(__FILE__) . '/ITypedObject.php';

    /**
     * SabreAMF_TypedObject 
     * 
     * @package SabreAMF 
     * @version $Id: TypedObject.php 233 2009-06-27 23:10:34Z evertpot $
     * @copyright Copyright (C) 2006-2009 Rooftop Solutions. All rights reserved.
     * @author Evert Pot (http://www.rooftopsolutions.nl/) 
     * @licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause) 
     * @uses SabreAMF_ITypedObject
     */
    class SabreAMF_TypedObject implements SabreAMF_ITypedObject {

        private $amfClassName;
        private $amfData;

        public function __construct($classname,$data) {

            $this->setAMFClassName($classname);
            $this->setAMFData($data);

        }

        /**
         * getAMFClassName 
         * 
         * @return string 
         */
        public function getAMFClassName() {

            return $this->amfClassName;

        }

        /**
         * getAMFData 
         * 
         * @return mixed 
         */
        public function getAMFData() {

            return $this->amfData;

        }

        /**
         * setAMFClassName 
         * 
         * @param string $classname 
         * @return void
         */
        public function setAMFClassName($classname) {

            $this->amfClassName = $classname;
            
        }

        /**
         * setAMFData 
         * 
         * @param mixed $data 
         * @return void
         */
        public function setAMFData($data) {

            $this->amfData = $data;

        }

    }


