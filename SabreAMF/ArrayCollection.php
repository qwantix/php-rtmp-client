<?php

    require_once 'SabreAMF/Externalized.php';

    /**
     * This is the default mapping for the flex.messaging.io.ArrayCollection class
     * It can be accessed using most of the normal array access methods
     * 
     * @package SabreAMF
     * @uses SabreAMF_Externalized
     * @uses IteratorAggregate
     * @uses ArrayAccess
     * @uses Countable
     * @version $Id: ArrayCollection.php 233 2009-06-27 23:10:34Z evertpot $
     * @copyright Copyright (C) 2007-2009 Rooftop Solutions. All rights reserved.
     * @author Evert Pot (http://www.rooftopsolutions.nl) 
     * @license licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause)
     */
    class SabreAMF_ArrayCollection implements SabreAMF_Externalized, IteratorAggregate, ArrayAccess, Countable {

        /**
         * data 
         * 
         * @var array 
         */
        private $data; 

        /**
         * Construct this object 
         * 
         * @param array $data pass an array here to populate the array collection 
         * @return void
         */
        function __construct($data = array()) {

            if (!$data) $data = array();
            $this->data = new ArrayObject($data);

        }

        /**
         * This is used by SabreAMF when this object is unserialized (from AMF3) 
         * 
         * @param array $data 
         * @return void
         */
        function readExternal($data) {

            $this->data = new ArrayObject($data);

        }

        /**
         * This is used by SabreAMF when this object is serialized 
         * 
         * @return array 
         */
        function writeExternal() {

            return iterator_to_array($this->data);

        }

        /**
         * implemented from IteratorAggregate 
         * 
         * @return ArrayObject 
         */
        function getIterator() {

            return $this->data;

        }

        /**
         * implemented from ArrayAccess 
         * 
         * @param mixed $offset 
         * @return bool 
         */
        function offsetExists($offset) {

            return isset($this->data[$offset]);

        }

        /**
         * Implemented from ArrayAccess 
         * 
         * @param mixed $offset 
         * @return mixed 
         */
        function offsetGet($offset) {

            return $this->data[$offset];

        }

        /**
         * Implemented from ArrayAccess 
         * 
         * @param mixed $offset 
         * @param mixed $value 
         * @return void
         */
        function offsetSet($offset,$value) {

            if (!is_null($offset)) {
                $this->data[$offset] = $value;
            } else {
                $this->data[] = $value;
            }

        }

        /**
         * Implemented from ArrayAccess 
         * 
         * @param mixed $offset 
         * @return void
         */
        function offsetUnset($offset) {

            unset($this->data[$offset]);

        }

        /**
         * Implemented from Countable 
         * 
         * @return int
         */
        function count() {

            return count($this->data);

        }

    }


