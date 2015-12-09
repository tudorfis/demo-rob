<?php

    /** Check if json
     * @param $in
     * @return bool
     */
    function isJson($in)
    {
        if (is_string($in)) {
            json_decode($in);
            return (json_last_error() == JSON_ERROR_NONE);
        }
        return false;
    }

    /**
     * Zend_Debug::dump()
     */
    function d($in)
    {
        Zend_Debug::dump($in);
    }

    /**
     * Zend_Debug::dump()
     * die()
     */
    function dd($in)
    {
        Zend_Debug::dump($in);
        die;
    }

    /**
     * json_encode()
     */
    function jd($in)
    {
        return json_encode($in);
    }

    /**
     * Check if is_null or empty
     * @param mixed $in
     */
    function isNE($in)
    {
        return (is_null($in) || empty($in));
    }

    /**
     * Return if isset
     * @param mixed $array
     * @param mixed $key
     */
    function risI($array, $key)
    {
        return (isset($array[$key])) ? $array[$key] : null;
    }

    /**
     * Get the resource link
     * @param string $link
     * @return string
     */
    function l($link = '') {
        return (!isNE($link) ? 'http://'. $_SERVER['HTTP_HOST'].
                (isset($_SERVER['CONTEXT_PREFIX']) ? $_SERVER['CONTEXT_PREFIX'] : '/master') .'/'. $link : '');
    }

    /**
     * Get the difference bettween 2 dates
     * @param $date1
     * @param $date2
     * @return array
     */
    function dateDiff($date1, $date2) {

        $diff = abs(strtotime($date2) - strtotime($date1));
        $years = floor($diff / (365*60*60*24));
        $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
        $days = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));

        $result = array(
            'years'     => $years,
            'months'    => $months,
            'days'      => $days
        );

        return $result;
    }

    /** Get difference days
     * @param $date1
     * @param $date2
     * @return float
     */
    function dateDiffDays($date1, $date2) {
        $diff = abs(strtotime($date2) - strtotime($date1));
        return floor(($diff) / (60*60*24));
    }

    /**
     * Translate
     * @param $l
     * @param $lang
     * @return string
     */
    function t($l, $lang) {
        return (isset($l[$lang]) ? $l[$lang] : '');
    }

    /**
     * Make Array 2 NVP
     * @param $_array
     */
    function makeArray2Nvp($_array) {
        $result = '';
        foreach ($_array as $key => $value) {
            $result .= $key.'='.$value.'&';
        }
        $result = trim($result, '&');
        return $result;
    }

    /** Make NVP 2 Array
     * @param $_nvp
     * @return array
     */
    function makeNvp2Array($_nvp) {
        $result = array();
        $_array = explode('&', $_nvp);
        foreach ($_array as $a) {
            $_a = explode('=', $a);
            $result[$_a[0]] = $_a[1];
        }
        return $result;
    }

    /** generate report file name */
    function generateReportFilename($language) {
        return date('Y-m-d').'_'.date('H:i:s').'_'.$language.'_'.substr(md5(mt_rand(0,9999)), 0, 6).'.pdf';
    }

    /**
     * Round with 2 decimals
     * @param $nr
     * @return string
     */
    function round2Nr($nr) {
        return sprintf('%0.2f', $nr);
    }
