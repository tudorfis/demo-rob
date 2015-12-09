<?php

class IndexController extends KM_Abstract
{
    public $contexts = array(
        'index'         => array('json'),
        'post'          => array('json'),
        'delete'        => array('json'),
        'query'         => array('json'),
        'save-image'    => array('json'),
        'sendmail'      => array('json'),
        'import-csv'    => array('json'),
    );

    protected $_permited_actions = array('sendmail');
    protected $_permited_tables = array();

    const SUCCESS   = '1';
    const WARNING   = '2';
    const ERROR     = '3';
    
    protected $type;
    protected $env;
    protected $method;
    protected $filter;
    protected $table;
    protected $join;
    protected $joins;
    protected $fields;
    protected $id;
    
    protected $dbTable;
    protected $response;

    protected function _makeSettings($_array)
    {
        $settingsArray = array();
        foreach ($_array as $s) {

            /** if array **/
            if (isset($s['t'])) {
                if ($s['t'] == 'array' && !empty($s['j']))
                {
                    $tbl = KM_Abstract::f($s['j']);
                    $_s = $tbl->selectBy(null, $s['v']);
                    $cn = str_replace('_id', '', $s['n']);
                    $settingsArray[$cn] = (!is_null($_s)) ? $_s->toArray() : $tbl->createRow()->toArray();
                }
            }
            $settingsArray[$s['n']] = $s['v'];

        }
        return $settingsArray;
    }

    public function init()
    {
        /* set timezone */
        if (!ini_get('date.timezone')) {
            date_default_timezone_set('GMT');
        }

        /* get permited table */
        $this->_permited_tables = include APPLICATION_PATH . '/configs/tables.php';

        /* get params */
        $this->_params = $this->getRequest()->getParams(); 

        /* convert params json */
        foreach($this->_params as $k => $v) {

            /* url decode */
            if (!is_array($v)) {
                $v = urldecode($v);
            }

            /* check if json */
            if (isJson($v)) {
                $this->_params[$k] = json_decode($v, true);
            }
        }

        $this->type    = 1;
        $this->env     = risI($this->_params, "env");
        $this->method  = risI($this->_params, "method");
        $this->filter  = risI($this->_params, "filter");
        $this->like    = risI($this->_params, "like");
        $this->col     = risI($this->_params, "col");
        $this->table   = risI($this->_params, "table");
        $this->join    = risI($this->_params, "join");
        $this->fields  = risI($this->_params, "fields");
        $this->id      = risI($this->_params, "id");
        $this->dbTable = null;

        /** permited actions without token or table */
        if (!in_array($this->_params['action'], $this->_permited_actions)) {

            if ($this->method == 'multiple_login') {
                $is_ok = true;
                $tables_arr = explode(',', $this->table);
                foreach ($tables_arr as $tbl) {
                    if (!in_array($tbl, $this->_permited_tables)) {
                        $is_ok = false;
                        break;
                    }
                }
                if (!$is_ok) {
                    $this->type = 2;
                    $this->response = array(
                        'data' => $this->_params,
                        'message' => 'Please provide accesible tables',
                        'type' => $this->type
                    );
                }

            } else {

                /** public tables && public table according to enviroment */
                if (isset($this->_params['table']) &&
                        !in_array($this->_params['table'], $this->_permited_tables)) {

                    /** if no token specified */
                    if (!isset($this->_params['token']) ||
                            isNE($this->_params['token'])) {

                        $this->type = 2;
                        $this->response = array(
                            'data' => $this->_params,
                            'message' => 'Please provide token',
                            'type' => $this->type
                        );

                        /** if not valid token */
                    } else {

                        $tokensTbl = $this->f('tokens');
                        $filter = array('token' => $this->_params['token'],
                                        'date_logout'   => null);
                        $tokens = $tokensTbl->selectBy($filter)->toArray();

                        if (empty($tokens)) {
                            $this->type = 2;
                            $this->response = array(
                                'data' => $this->_params,
                                'message' => 'Invalid token specified',
                                'type' => $this->type
                            );
                        }
                    }

                }
            }

            /** if no table specified **/
            if ($this->type == 1 &&
                    (!isset($this->_params['table'])
                                || isNE($this->table))) {

                /** permit certain actions */
                if (!in_array($this->_params['action'], array('reports'))) {
                    $this->type = 2;
                    $this->response = array(
                        'data' => $this->_params,
                        'message' => 'Please specify table',
                        'type' => $this->type
                    );
                }
            }

        }

        $this->joins = include(APPLICATION_PATH . '/configs/joins.php');

        $contextSwitch = $this->_helper->getHelper('ContextSwitch');
        $contextSwitch->initContext('json');
    }
    
    public function postDispatch() {
        $this->view->assign("r", $this->response);
    }
    
    public function indexAction() {
       
       /* if response success */
       if ($this->type == 1) {

           $n_data = array();
           $table_arr = explode(',', $this->table);

           foreach($table_arr as $table) {

               $_array = array();
               $data = array();
               $_data = array();

               $this->table = $table;
               $this->dbTable = $this->f($this->table);

               /* map all */
               switch ($this->method)
               {
                   /* Make all joins */
                   case 'map_all':

                       // get joins
                       if (isset($this->joins[$this->table])) {
                           $this->join = $this->joins[$this->table];
                       }
                       break;

                   // get last id
                   case 'last_id':
                       $_array = $this->dbTable->getLastId();
                       break;

                   default:
                       break;
               }

               /** if not from  method */
               if (empty($_array)) {

                   /* get by id */
                   if (!isNE($this->id)) {
                       $_array = $this->dbTable
                           ->selectBy($this->filter, $this->id, $this->join, $this->like, $this->col, $this->method)
                           ->toArray();

                       $_array = array($_array);

                   /* get all */
                   } else {

                       $_array = $this->dbTable
                           ->selectBy($this->filter, null, $this->join, $this->like, $this->col, $this->method)
                           ->toArray();

                   }
               }

               /** build settings special case **/
               if (preg_match('/settings/', $this->table)) {

                   $_data = $this->_makeSettings($_array);

               /** build joins **/
               } elseif (!isNE($this->join)) {
                   if (!empty($_array)) {

                       $id_array = array();
                       foreach($_array as $key => $item) {
                           foreach($item as $col => $v) {
                               if (strpos($col,'|') !== false) {
                                   $e = explode('|', $col);
                                   $j_table = $e[0];
                                   $j_cn = $e[1];
                                   $j_col = $e[2];
                                   $j_cn_ = str_replace('_id', '', $j_cn);
                                   $item[$j_cn_][$j_col] = $v;
                                   unset($item[$col]);
                                   $id_array[$j_cn_] = $j_cn;
                               }
                           }

                           /** set ids */
                           foreach ($id_array as $table => $id) {
                               $item[$table]['id'] = $item[$id];
                           }

                           /** get with value */
                           if (isset($this->_params['get_with'])) {
                               if (isset($item[$this->_params['get_with']])) {
                                   $_data[$item[$this->_params['get_with']]] = $item;
                               } else {
                                   $_data[] = $item;
                               }
                           } else {
                               $_data[] = $item;
                           }
                       }

                       /**
                        * join deeper columns
                        * &join_deep[]=pet,pet_breed_id,pet_breed
                        * - in table, in id, table to join
                        */
                       if (isset($this->_params['join_deep'])) {

                           $__data = $_data;
                           $_data = array();

                           foreach ($__data as $item_k => $item) {
                               foreach ($this->_params['join_deep'] as $jd) {
                                   $jd_a = explode(',', $jd);
                                   $col = $item[$jd_a[0]][$jd_a[1]];
                                   $tbl = KM_Abstract::f($jd_a[2]);
                                   $res =  $tbl->selectBy(null, $col)->toArray();
                                   $item[$jd_a[0]][$jd_a[2]] = $res;
                               }
                               $_data[] = $item;
                           }
                       }
                   }

               /** default empty array **/
               } else {
                   if (!empty($_array)) {

                       /** get with value */
                       if (isset($this->_params['get_with'])) {
                           foreach($_array as $key => $item) {
                               $_data[$item[$this->_params['get_with']]] = $item;
                           }
                       } else {
                           $_data = $_array;
                       }
                   } else {
                       $_data = array();
                   }
               }

               $data = $_data;

               /** if multiple request */
               if (count($table_arr) > 1) {
                   $n_data[$this->table]['data'] = $data;
                   $n_data[$this->table]['count'] = count($data);

               /** single request */
               } else {
                   $n_data = $data;
               }

           }

           /* send response */
           if (count($table_arr) > 1) {
               $this->response = array(
                   'multiple' => $n_data,
                   'message' => 'Success',
                   'type' => 1
               );
           } else {
               $this->response = array(
                   'data' => $n_data,
                   'count' => count($n_data),
                   'message' => 'Success',
                   'type' => 1
               );
           }

       }

    }

    public function postAction() {

        /* if response success */
        if ($this->type == 1) {

            $exists = false;
            $this->dbTable = $this->f($this->table);
            $isSettings = preg_match('/settings/', $this->table) ? true : false;

            /** by id */
            if (isset($this->_params['id'])
                && !isNE($this->_params['id'])) {

                $i = $this->dbTable->selectBy(null, $this->_params['id']);

            /** by custom fields */
            } elseif (isset($this->filter['id_n'])
                    && isset($this->filter['id_v'])) {

                $this->filter = array_merge($this->filter, array('single_row' => 1));
                $i = $this->dbTable->selectBy($this->filter);
                if (is_null($i)) {
                    $i = $this->dbTable->createRow();
                }

            /** create new field **/
            } else {
                $i = $this->dbTable->createRow();
            }

            /** if in settings */
            if ($isSettings) {
                $settings_data = array();
                foreach ($this->_params['data'] as $n => $v) {
                    if (!isNE($n)) {
                        $this->filter = array('n' => $n, 'single_row' => 1);
                        $i =  $this->dbTable->selectBy($this->filter);
                        $i_check = $i->toArray();

                        /** if exists */
                        if (!isNE($i_check['id'])) {
                            $i->setData('v', $v);
                            $i->save();
                            $settings_data[$n] = $v;

                        /** if create */
                        } elseif ($this->method == 'add_new') {

                            $i = $this->dbTable->createRow();
                            $i->setData('n', $n);
                            $i->setData('v', $v);
                            $i->setData('t', 'text');
                            $i->save();
                            $settings_data[$n] = $v;
                        }
                    }
                }

                $this->response = array(
                    'data' => $settings_data,
                    'message' => 'Success',
                    'type' => $this->type
                );

            /** if normal post */
            } else {

                if (isset($this->_params['data'])
                    && !empty($this->_params['data']))
                {

                    /** check if exists */
                    if ($this->method == 'taxonomy') {
                        foreach ($this->_params['data'] as $k => $v) {
                            $filter[$k] = $v;
                        }
                        $e = $this->dbTable->selectBy($filter);
                        if (!isNE($e->toArray())) {
                            $exists = true;
                        }
                    }

                    /** save values */
                    if (!$exists) {

                        foreach ($this->_params['data'] as $k => $v) {
                            $i->setData($k, $v);
                        }
                        $i->save();
                        if ($i_id = $i->save()) {
                            $this->response = array(
                                'data' => $i->toArray(),
                                'message' => 'Success',
                                'type' => $this->type
                            );
                        } else {
                            $this->type = 3;
                            $this->response = array(
                                'data' => $this->_params,
                                'message' => "Something wen't wrong",
                                'type' => $this->type
                            );
                        }
                    }


                } else {
                    $this->type = 2;
                    $this->response = array(
                        'data' => array(),
                        'message' => "Please provide data",
                        'type' => $this->type
                    );
                }

                if ($exists) {
                    $this->type = 2;
                    $this->response = array(
                        'data' => array(),
                        'message' => "Taxonomy already saved",
                        'type' => $this->type
                    );
                }
            }
        }
    }

    public function deleteAction() {

        /** if by filter */
        if (!isNE($this->filter)) {

            $this->dbTable = $this->f($this->table);
            $row = $this->dbTable->selectBy($this->filter);

        /** if by id **/
        } elseif (!isNE($this->id)) {

            $this->dbTable = $this->f($this->table);
            $row = $this->dbTable->selectBy(null, $this->id);
            if ($this->method != 'permanent') {
                $row->status_id = 3;
            }


        } else {
            $this->type = 2;
            $this->response = array(
                'data' => $this->_params,
                'message' => 'Please provide id',
                'type' => $this->type
            );
        }

        /** if success */
        if ($this->type == 1) {

            $result = ($this->method == 'permanent')
                        ? $row->delete() : $row->save();

            if ($result) {
                $this->response = array(
                    'data' => $this->_params,
                    'message' => 'Row successfully deleted',
                    'type' => $this->type
                );
            } else {
                $this->type = 3;
                $this->response = array(
                    'data' => $this->_params,
                    'message' => 'Unable to modify row',
                    'type' => $this->type
                );
            }
        }
    }

    public function queryAction() {

        /* if response success */
        if ($this->type == 1) {

            if (isset($this->_params['query'])
                && !isNE($this->_params['query'])) {

                $query = $this->_params['query'];
                $config = Zend_Registry::get("__CONFIG__");
                $db_config = $config['resources']['db']['params'];

                $mysqli = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['dbname']);
                if ($this->method == 'multi_query') {
                    $r = $mysqli->multi_query($query);
                } else {
                    $r = $mysqli->query($query);
                }
            }
        }
    }
    
    public function saveImageAction() {

        /* if response success */
        if ($this->type == 1) {

            $params = $this->getRequest()->getParams();

            $response = KM_SaveImages::saveImageBase64(
                                $this->_params['image_src'],
                                $this->_params['image_type'],
                                (isset($this->_params['w']) ? $this->_params['w'] : ''),
                                (isset($this->_params['h']) ? $this->_params['h'] : '')
                            );

            if (!is_null($response)) {
                $this->response = array(
                    'data' => $response,
                    'message' => 'Image successfully uploaded',
                    'type' => $this->type
                );
            } else {
                $this->type = 3;
                $this->response = array(
                    'data' => array(),
                    'message' => 'Something went wrong',
                    'type' => $this->type
                );
            };
        }
    }

    public function sendmailAction() {

        /* if response success */
        if ($this->type == 1) {

            $subject    = (isset($this->_params['subject']))     ? $this->_params['subject']  : '';
            $template   = (isset($this->_params['template']))    ? $this->_params['template'] : '';
            $email      = (isset($this->_params['email']))       ? $this->_params['email']    : '';
            $p          = (isset($this->_params['p']))           ? $this->_params['p']        : array();

            /** if no template */
            if (isNE($template)) {

                $this->type = 2;
                $this->response = array(
                    'data' => array(),
                    'message' => 'Please specify template',
                    'type' => $this->type
                );

            /** if no email */
            } elseif (isNE($email)) {

                $this->type = 2;
                $this->response = array(
                    'data' => array(),
                    'message' => 'Please specify email',
                    'type' => $this->type
                );

            /** Try SEND with htmlMailer */
            } else {

                $htmlMailer = new KM_HtmlMailer();
                $email_response = $htmlMailer->sendMail($subject, $template, $p, $email);

                /** Success */
                if (!is_null($email_response)) {
                    $this->type = 1;
                    $this->response = array(
                        'data' => array(),
                        'message' => 'Email succesfully send',
                        'type' => $this->type
                    );

                /** Fail */
                } else {
                    $this->type = 3;
                    $this->response = array(
                        'data' => array(),
                        'message' => "Something wen't wrong, check logme.log",
                        'type' => $this->type
                    );
                }


            }
        }
    }

    public function importCsvAction() {

        /* if response success */
        if ($this->type == 1) {
            $params = $this->getRequest()->getParams();

            if (isset($params['file'])) {
                $encodedString = utf8_decode($this->_params['file']);
                $file_array = explode('base64,',$encodedString);

                $file_decoded = base64_decode($file_array[1]);
                $lines = explode("\n",$file_decoded);
                $column_lines = explode(',',$lines[0]);

                unset($lines[0]);
                $this->dbTable = $this->f($this->table);
                $result = array();

                $query = '';
                foreach ($lines as $l) {
                    if (!empty($l)) {
                        $row = $this->dbTable->createRow();
                        $line = explode(',', $l);
                        foreach ($line as $col_k => $col_v) {
                            $col_k = preg_replace('/\s+/','', str_replace('"', '', $column_lines[$col_k]));
                            $col_v = str_replace('"', '', $col_v);
                            $row->{$col_k} = $col_v;
                        }
                        $row_id = $row->save();
                        $result[$row_id] = $row->toArray();
                    }
                }

                $this->type = 1;
                $this->response = array(
                    'data' => $result,
                    'message' => 'Result',
                    'type' => $this->type
                );
            }

        }
    }
}