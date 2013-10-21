<?php

    /**
     * SabreAMF_Exception 
     * 
     * @package SabreAMF
     * @version $Id: DetailException.php 233 2009-06-27 23:10:34Z evertpot $
     * @copyright Copyright (C) 2006-2009 Rooftop Solutions. All rights reserved.
     * @author Evert Pot (http://www.rooftopsolutions.nl) 
     * @author Renaun Erickson (http://renaun.com/blog)
     * @license licence http://www.freebsd.org/copyright/license.html  BSD License (4 Clause)
     */

    /**
     * This interface can provide detailed information about an exception
     * Implement this interface to provide faultDetail in flex2 and detail in Flash Remoting
     */
    interface SabreAMF_DetailException {

        /**
         * Returns detailed information about the exception 
         * 
         * @return void
         */
        function getDetail();

    }


