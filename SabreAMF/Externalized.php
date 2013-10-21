<?php

    /**
     * Implement this interface to construct your own Externalized objects..
     * Don't forget to map the object using the classmapper
     * 
     * @package SabreAMF
     * @version $Id: Externalized.php 233 2009-06-27 23:10:34Z evertpot $
     * @copyright Copyright (C) 2007-2009 Rooftop Solutions. All rights reserved.
     * @author Evert Pot (http://www.rooftopsolutions.nl) 
     * @license licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause)
     */
    interface SabreAMF_Externalized {

        /**
         * This method is called when the object is serialized
         *
         * @return mixed
         */
        function writeExternal();

        /**
         * This method is called when the object is unserialized 
         * 
         * @param mixed $data 
         * @return void
         */
        function readExternal($data);

    }


