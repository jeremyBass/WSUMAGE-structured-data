<?php
class Wsu_StructuredData_Model_VCard extends Mage_Core_Model_Abstract {
    // Helper
    private $rdff = NULL; // RDF Format Helper
    private $sysinfo = NULL; // Magento System Information Helper
    protected $_data = array();
    protected $_address = array();
    private $baseURL;
    private $URIs;
    public function __call($method, $args) {
        $output = "VCard->" . $method . " (";
        foreach ($args as $arg) {
            $output .= $arg . ",";
        }
        $output .= ")";
        webdirx_div::message($output);
    }
    protected function _construct() {
        $this->_init('structureddata/vcard');
        // helper
        $this->sysinfo = Mage::app()->getHelper('structureddata/Sysinfo');
        //$this->rdff = Mage::app()->getHelper('structureddata/Rdfaformat');
    }
    public function setRdff($rdffClassName) {
        $this->rdff = Mage::app()->getHelper($rdffClassName);
    }
    // Setter and getter for: Streetaddress
    public function setStreetaddress($val) {
        $this->_address['street-address'] = $val;
        return $this;
    }
    public function getStreetaddress() {
        return $this->_address['street-address'];
    }
    // Setter and getter for: Postalcode
    public function setPostalcode($val) {
        $this->_address['postal-code'] = $val;
        return $this;
    }
    public function getPostalcode() {
        return $this->_address['postal-code'];
    }
    // Setter and getter for: Locality
    public function setLocality($val) {
        $this->_address['locality'] = $val;
        return $this;
    }
    public function getLocality() {
        return $this->_address['locality'];
    }
    // Setter and getter for: Countryname
    public function setCountryname($val) {
        // translate countryname
        $translatedCountryName          = Mage::getModel('directory/country')->loadByCode($val)->getName();
        $this->_address['country-name'] = $translatedCountryName;
        return $this;
    }
    public function getCountryname() {
        return $this->_address['country-name'];
    }
    // Setter and getter for: Tel
    public function setTel($val) {
        $this->_data['tel'] = $val;
        return $this;
    }
    public function getTel() {
        return $this->_data['tel'];
    }
    // Setter and getter for: Email
    public function setEmail($val) {
        $this->_data['email'] = $val;
        return $this;
    }
    public function getEmail() {
        return $this->_data['email'];
    }
    /** vCard ***********************************/
    public function toXHTML($subjectURI) {
        $vcard         = "";
        // replacements in default attributes from rdff
        $replacements  = array(
            "{lang}" => $this->sysinfo->getLocaleCode()
        );
        $propertyArray = array();
        // (1) the vcard:Address class
        $class         = "vcard:Address"; //Resources that are vCard (postal) addresses
        forEach ($this->_address as $key => $value) {
            if ($value != "") {
                $property                 = "vcard:$key";
                $propertyArray[$property] = $value;
            }
        }
        $statements   = $this->rdff->properties($propertyArray, $replacements);
        $vcardAddress = $this->rdff->wrapStatements($statements, $subjectURI, $class); //Resources that are vCard (postal) addresses
        $vcard .= $this->rdff->wrapRel($vcardAddress, "vcard:adr"); // A postal or street address of a person
        forEach ($this->_data as $key => $value) {
            if ($value != "") {
                $vcard .= $this->rdff->propertyAuto("vcard:$key", $value, $replacements);
            }
        }
        return $vcard;
    }
    public function url($url) {
        $vcardUrl = $this->rdff->rel("vcard:url", $url);
        return $vcardUrl;
    }
}