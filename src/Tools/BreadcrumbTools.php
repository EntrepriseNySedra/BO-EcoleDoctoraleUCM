<?php

namespace App\Tools;

/**
 * Description of Breadcrumb
 *
 * @author Joelio
 */
class BreadcrumbTools {
    
    protected $label = '' ;
    protected $href = '' ;
    
    public function __construct($label, $href = '') {
        $this->label = $label;
        $this->href = $href;
    }
    
    function getLabel() {
        return $this->label;
    }

    function getHref() {
        return $this->href;
    }

    function setLabel($label) {
        $this->label = $label;
        return $this;
    }

    function setHref($href) {
        $this->href = $href;
        return $this;
    }


    
}
