<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4 cc=76; */

/**
 * @package     omeka
 * @subpackage  neatline
 * @copyright   2012 Rector and Board of Visitors, University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html
 */

abstract class Neatline_Row_Tag extends Neatline_Row_Abstract
{


    public $name; // VARCHAR(100) NULL


    /**
     * Set the `name` field.
     *
     * @param string $tag The tag name.
     */
    public function __construct($name = null)
    {
        parent::__construct();
        $this->name = $name;
    }


}