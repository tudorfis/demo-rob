<?php

class Table_Abstract extends Zend_Db_Table 
{
    protected $_name = '';
    protected $_rowClass = '';
    
    protected $count;
    protected $pages;
    protected $limit;
    protected $result;
    protected $query;

    protected $_unpermited_filters = array('status_id', 'no_status', 'limit',
                                'limit_count', 'limit_offset', 'order',
                                'single_row', 'join_like_table', 'join_like_id');

    protected $_unpermited_joins = array();

    public function __construct($config = array(), $definition = null) {

        parent::__construct($config,$definition);
        
        $this->setDbModel('Model_Abstract');
        
        $config = Zend_Registry::get("__CONFIG__");

        if ( isset( $config["page"]["limit"] ) ){
            $this->limit = $config["page"]["limit"];
        }
        else{
            $this->limit = 20;
        }
    }
    
    /**
    * Set db table
    */
    public function setDbTable($tbl) {
        $this->_name = $tbl;
    }
    
    /**
    * Set model
    */
    public function setDbModel($model) {
        $this->_rowClass = $model;
    }
   
    /**
    * Get Query
    */
    public function getQuery() {
        return $this->query;
    }
   
    /**
    * Get Result 
    */
    public function getResult() {
        return $this->result;
    }
   
    /**
    * Encrypt password
    */
    public static function encryptPassword($password) {
        return md5($password);
    }

    /**
     * Do a query
     * @param $query
     * @return bool
     */
    public function doQuery($query, $col = '') {
        $array = array();
        $_array = $this->_db->query($query)->fetchAll();
        if (!empty($col)) {
            foreach($_array as $a) {
                $array[$a[$col]] = $a;
            }
            return $array;
        };
        return $_array;
    }

    /**
    * Get all values, or just by id from table by filter
    * status is always <> deleted
    * @param mixed $filter
    */
    public function selectBy($filter = null, $id = null, $join = null, $like = null, $col = null, $method = null)
    {

        // safety measure
        if (!is_array($filter)) {
            $filter = array();
        }
        
        if (!is_array($join)) {
            $join = array();
        }

        /** If by columns */
        if (!isNE($col)) {
            $col_ = array();
            foreach ($col as $k => $v) {
                if (in_array($k, $this->info('cols'))) {
                    $col_[] = $k;
                }
            }
            $select = $this->select()
                            ->setIntegrityCheck(false)
                            ->from(array("t" => $this->_name), $col_);
        } else {
            $select = $this->select()
                            ->setIntegrityCheck(false)
                            ->from(array("t" => $this->_name));
        }

        // If by id
        if (!is_null($id)) {
            $select->where("t.id = ?", $id);
        }

        // select by status id
        if (in_array('status_id', $this->info('cols'))) {
            if(!empty($filter["status_id"]) ){
                $select->where("t.status_id = ?", $filter["status_id"]);
                // where status <> deleted if has column status
            } else {
                $select->where("t.status_id <> '3'");
            }
        }

        // iterate filter
        foreach($filter as $k => $v) {
            if (!in_array($k, $this->_unpermited_filters)) {

                /* check if column exists */
                if (in_array($k, $this->info('cols'))) {

                    /* encrypt password */
                    if($k == 'password' && !empty($v)) {
                        $v = Table_Abstract::encryptPassword($v);
                    }

                    /** check for null */
                    if ($v === null) {
                        $select->where("t.$k is null");
                    } else {
                        $select->where("t.$k = ?", $v);
                    }
                }

            }
        }
        
        // iterate join
        $join['tbl'] = $join;
        foreach ($join['tbl'] as $table => $table_id) {
            if (!in_array($table, $this->_unpermited_joins)) {

                $joinDbTable = new Table_Abstract();
                $joinDbTable->setDbTable($table);
                $tableJoins = $joinDbTable->info('cols');

                /* build colums */
                $columns = array();
                foreach ($tableJoins as $j) {
                    if (!in_array($j, array('id', 'status_id'))) {
                        $columns[$table.'|'.$table_id.'|'.$j] = $j;
                    }
                }

                $alias_name = APPLICATION_DB.substr(md5(mt_rand(0,9999)), 0, 6);
                $join['alias'][$table] = $alias_name;

                $select->joinLeft(array($alias_name => $table),
                        "t.$table_id = $alias_name.id", $columns);

                unset($join[$table]);

            }
        }

        // iterate like
        $like_req = '';
        if (!isNE($like)) {

            // if with join
            if (isset($join['alias']) && $method != 'map_all') {
                foreach($like as $table => $table_v) {
                    foreach ($table_v as $k => $v) {
                        $alias_name = $join['alias'][$table];
                        $like_req .= "$alias_name.$k like '%$v%' or ";
                    }
                }
            } else {
                foreach($like as $k => $v) {
                    $like_req .= "t.$k like '%$v%' or ";
                }
            }
            $like_req = trim($like_req, ' or ');
            $select->where($like_req);
        }

        // Limit
        if (isset($filter['limit']) && !empty($filter['limit'])) {
            $select->limit($filter['limit']);
        }

        // Limit count & offset
        if (isset($filter['limit_count']) && isset($filter['limit_offset'])) {
            $select->limit($filter['limit_count'], $filter['limit_offset']);
        }

        // Order by
        if (isset($filter['order'])) {
            $select->order("t.".$filter['order']);
        } else {
            $select->order("t.id desc");
        }

        // Save query
        $this->query = $select->__toString();

        // By Id
        if (!is_null($id) ||
                (isset($filter['single_row']) && $filter['single_row'] == 1)) {
            $this->result = $this->fetchRow($select);
            return (!isNE($this->result)) ? $this->result : $this->createRow();
        } else {
            return $this->result =  $this->fetchAll($select);
        }

    }

    /**
     * Get last id
     */
    public function getLastId() {
        $array1 = array();
        $array2 = $this->createRow()->toArray();
        $query = "select max(id) as max from `". $this->_name ."`;";
        if ($r = $this->_db->query($query)) {
            $array1 = $r->fetch();
        }
        return $array1 + array('el' => $array2);
    }
    
    /**
    * Delete by id
    * @params $id
    */
    public function deleteById($id) {
        $stmt = $this->_db->query("delete from `".$this->_name ."` where `id` = '".$id."'");
        return $stmt->execute();
    }


    /**
    * Set the count
    * 
    * @param mixed $count
    * @return Table_Abstract
    */
    public function setCount($count)
    {
        $this->count = $count;
        return $this;
    }

    /**
    *  Gets the count
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
    * Gets the pages
    */
    public function getPages()
    {
        $this->pages = ceil($this->count / $this->limit);
        return $this->pages;
    }

    /**
    * Sets the limit
    * 
    * @param mixed $limit
    * @return Table_Abstract
    */
    public function setLimit($limit)
    {
        if (!isNE($limit)) {
            $this->limit = $limit;
        }
        return $this;
    }

    /**
    * Gets the limit
    */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
    * Get total number
    */
    public function getTotal() {
        
        $array = array('c' => '');
        $query = "select count(*) as c from `". $this->_name ."` 
                    where `status` <> '3';";
                     
         if ($r = $this->_db->query($query)) {
            $array = $r->fetch();
         }
         return $array['c'];   
    }
    

}
