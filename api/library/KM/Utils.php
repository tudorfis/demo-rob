<?php

class KM_Utils
{
    /**
    * Puts http:// on begining of the link
    * @param mixed $link
    * @return mixed
    */
    public static function putHttpOnBeginingOfLink($link) 
    {
        if (!preg_match('/mailto:/', $link)) {
            return (!preg_match('/https?\:\/\//',$link) && !empty($link)) ? 'http://'.$link : $link;       
        } else {
            return $link;
        }
    }

    /**
    * Matches link
    * @param mixed $in
    */
    public static function getLinkMatch($in) {
        preg_match('/((([A-Za-z]{3,9}:(?:\/\/)?)(?:[-;:&=\+\$,\w]+@)?[A-Za-z0-9.-]+|(?:www.|[-;:&=\+\$,\w]+@)[A-Za-z0-9.-]+)((?:\/[\+~%\/.\w-_]*)?\??(?:[-\+=&;%@.\w_]*)#?(?:[\w]*))?)/', $in, $out);
        return $out;
    }


    /**
    * Get real ip address under router
    * @return ip address
    */
    public static function getRealIp() {
        if (! empty($_SERVER['HTTP_X_FORWARED_FOR'])) {
            $client_ip = $_SERVER['HTTP_X_FORWARED_FOR'];
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $client_ip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $client_ip = $_SERVER['REMOTE_ADDR'];
        }
        return $client_ip;
    }

    /**
    * Returns true or false from 0 and 1
    * @param number
    * @return boolean
    */
    public static function TrueOrFalse($nr) {
        if ($nr == 1) {
            return true;
        } else if ($nr == 0 || $nr == '') {
            return false;
        }
    }

    /**
    * scaleCoordonateToMap()
    * Converts coordonate to map dimensions
    *   
    * @param mixed $coordonate_x_y - x_start, x_end, y_start, y_end
    * @param mixed $width_height - mse_width, mse_length
    * @param mixed $map_width_height - map_image['width'], map_image['height']
    */
    public static function scaleCoordonateToMap($coordonate_x_y, $width_height, $map_width_height) {
        $scale = (100 * $coordonate_x_y) / $width_height;
        return ($scale * $map_width_height) / 100;  
    }

    /**
    * scaleMapDimension()
    * Converts map dimension to coordonate
    *   
    * @param mixed $coordonate_x_y - x_start, x_end, y_start, y_end
    * @param mixed $width_height - mse_width, mse_length
    * @param mixed $map_width_height - map_image['width'], map_image['height']
    */
    public static function scaleMapDimension($coordonate_x_y, $map_width_height, $width_height) {
        $scale = (100 * $coordonate_x_y) / $map_width_height;
        return ($scale * $width_height) / 100;  
    }


    /**
    * Calculates percentage for showing statistics
    * @param mixed $val
    * @param mixed $max
    */
    public static function calcPerc($val, $max, $minus = 0) {
        return  ($max != 0) ? ($val * 100 / $max) - $minus : 0;
    }

    public static function showNr($in) {
        return ($in != 0) ? $in : '';
    }

    /**
    * Check if mobile version
    * @return $is_mobile
    */
    public static function isMobile() {

        $is_mobile = 0;

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            if(preg_match('/(android|up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
                $is_mobile=1;
            }
        }

        if (isset($_SERVER['HTTP_ACCEPT'])) {
            if((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml')>0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
                $is_mobile=1;
            }
        }

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));
            $mobile_agents = array('w3c ','acs-','alav','alca','amoi','andr','audi','avan','benq','bird','blac','blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno','ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-','maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-','newt','noki','oper','palm','pana','pant','phil','play','port','prox','qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar','sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-','tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp','wapr','webc','winw','winw','xda','xda-');

            if(in_array($mobile_ua,$mobile_agents)) {
                $is_mobile = 1;
            }   
        }


        if (isset($_SERVER['ALL_HTTP'])) {
            if (strpos(strtolower($_SERVER['ALL_HTTP']),'OperaMini')>0) {
                $is_mobile = 1;
            }
        }

        return $is_mobile;
    }
    
    /**
    * Generate random password
    */
    public static function randomPassword() {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }
    
    /**
    * @TODO
    * Get device according to user-agent
    */
    public static function getDeviceId() {
        /** set default for web **/
        return 1;     
    }
    
    /**
    * Get tokken from phpsessid
    */
    public static function getTokenFromSession() {
        return $_COOKIE['PHPSESSID'];     
    }


}