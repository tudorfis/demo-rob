<?php

class Model_Abstract extends Zend_Db_Table_Row_Abstract
{
    /**
     * Return @v
     */
    public function __toString() {
        return $this->_data['v'];
    }

    /**
     * Set data
     *
     * @param mixed $k
     * @param mixed $v
     */
    public function setData($k, $v) {

        /** if column exists */
        if (isset($this->{$k})) {

            /** if password & !empty */
            if ($k == 'password' && !empty($v)) {

                $v = Table_Abstract::encryptPassword($v);
                $this->{$k} = $v;

            } elseif ($k != 'password') {

                /** @var set key value pair */
                $this->{$k} = $v;
            }

        }
    }
}