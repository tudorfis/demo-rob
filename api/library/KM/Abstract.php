<?php

abstract class KM_Abstract extends Zend_Controller_Action 
{
    protected $_config;
    protected $_identity;
    protected $_params;
    protected $_settings;

    public function init() {
        
        /* Initialize config */
        $this->_config = Zend_Registry::get('__CONFIG__');
        Zend_Layout::getMvcInstance()->getView()->config = $this->_config;
        
        /* Initialise params */
        $this->_params = $this->getRequest()->getParams();
        $this->_params['view'] = (!isset($this->_params['view']) ? '' : $this->_params['view']);
        Zend_Layout::getMvcInstance()->getView()->params = $this->_params;

    }        
    
    public function preDispatch() {
        
    }

    /**
     * factory tables
     * @param $tableName
     * @return mixed
     * @throws Exception
     */
    static protected $_tablesObj = array();

    static public function f($tableName) {

        if  (!array_key_exists($tableName, self::$_tablesObj)) {

            $classDefaultName = "Table_Abstract";
            $classExistsName  = "Table_".ucfirst($tableName);
            $className = (class_exists($classExistsName)) ? $classExistsName : $classDefaultName;

            $tableObj = new $className;
            $tableObj->setDbTable($tableName);
            $tableObj->setDbModel("Model_Abstract");

            self::$_tablesObj[$tableName] = $tableObj;
        }
        return self::$_tablesObj[$tableName];
    }
    
}
