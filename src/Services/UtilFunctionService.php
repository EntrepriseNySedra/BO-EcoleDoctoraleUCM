<?php

namespace App\Services;

/**
 * Description of UtilFunctionService.php.
 *
 * @package App\Services
 * @author joelio
 */
class UtilFunctionService
{

    const MIN_LENGTH = 6;

    /**
     * MASK FORMAT [XXX-XXX]
     * 'X' this is random symbols
     * '-' this is separator
     *
     * @param array $options
     *
     * @return string
     * @throws Exception
     */
    public static function generate($options = [])
    {

        $length       = (isset($options['length']) ? filter_var($options['length'], FILTER_VALIDATE_INT, ['options' => ['default' => self::MIN_LENGTH, 'min_range' => 1]]) : self::MIN_LENGTH);
        $prefix       = (isset($options['prefix']) ? self::cleanString(filter_var($options['prefix'], FILTER_SANITIZE_STRING)) : '');
        $suffix       = (isset($options['suffix']) ? self::cleanString(filter_var($options['suffix'], FILTER_SANITIZE_STRING)) : '');
        $useLetters   = (isset($options['letters']) ? filter_var($options['letters'], FILTER_VALIDATE_BOOLEAN) : true);
        $useNumbers   = (isset($options['numbers']) ? filter_var($options['numbers'], FILTER_VALIDATE_BOOLEAN) : false);
        $useSymbols   = (isset($options['symbols']) ? filter_var($options['symbols'], FILTER_VALIDATE_BOOLEAN) : false);
        $useMixedCase = (isset($options['mixed_case']) ? filter_var($options['mixed_case'],FILTER_VALIDATE_BOOLEAN) : false);
        $mask         = (isset($options['mask']) ? filter_var($options['mask'], FILTER_SANITIZE_STRING) : false);

        $uppercase = ['Q', 'W', 'E', 'R', 'T', 'Y', 'U', 'I', 'O', 'P', 'A', 'S', 'D', 'F', 'G', 'H', 'J', 'K', 'L', 'Z', 'X', 'C', 'V', 'B', 'N', 'M'];
        $lowercase = ['q', 'w', 'e', 'r', 't', 'y', 'u', 'i', 'o', 'p', 'a', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'z', 'x', 'c', 'v', 'b', 'n', 'm'];
        $numbers   = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $symbols   = ['`', '~', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '-', '_', '=', '+', '\\', '|', '/', '[', ']', '{', '}', '"', "'", ';', ':', '<', '>', ',', '.', '?'];

        $characters = [];
        $coupon     = '';

        if ($useLetters) {
            if ($useMixedCase) {
                $characters = array_merge($characters, $lowercase, $uppercase);
            } else {
                $characters = array_merge($characters, $uppercase);
            }
        }

        if ($useNumbers) {
            $characters = array_merge($characters, $numbers);
        }

        if ($useSymbols) {
            $characters = array_merge($characters, $symbols);
        }

        if ($mask) {
            for ($i = 0; $i < strlen($mask); $i ++) {
                if ($mask[$i] === 'X') {
                    $coupon .= $characters[mt_rand(0, count($characters) - 1)];
                } else {
                    $coupon .= $mask[$i];
                }
            }
        } else {
            for ($i = 0; $i < $length; $i ++) {
                $coupon .= $characters[mt_rand(0, count($characters) - 1)];
            }
        }

        return $prefix . $coupon . $suffix;
    }

    /**
     * @param        $length
     * @param string $prefix
     *
     * @return string
     */
    public static function generateUid($length, $prefix = '')
    {
        $options['length']  = $length;
        $options['numbers'] = true;

        return $prefix . strtolower(self::generate($options));
    }

    /**
     * @param int   $maxNumberOfCoupons
     * @param       $filename
     * @param array $options
     */
    public static function generateCouponsToXls($maxNumberOfCoupons = 1, $filename, $options = [])
    {
        $filename = (empty(trim($filename)) ? 'coupons' : trim($filename));

        header('Content-Type: application/vnd.ms-excel');

        echo 'Coupon Codes' . "\t\n";
        for ($i = 0; $i < $maxNumberOfCoupons; $i ++) {
            $temp = self::generate($options);
            echo $temp . "\t\n";
        }

        header('Content-disposition: attachment; filename=' . $filename . '.xls');
    }

    /**
     * Strip all characters but letters and numbers
     *
     * @param       $string
     * @param array $options
     *
     * @return string
     * @throws Exception
     */
    private static function cleanString($string, $options = [])
    {
        $toUpper = (isset($options['uppercase']) ? filter_var($options['uppercase'], FILTER_VALIDATE_BOOLEAN) : false);
        $toLower = (isset($options['lowercase']) ? filter_var($options['lowercase'], FILTER_VALIDATE_BOOLEAN) : false);

        $striped = preg_replace('/[^a-zA-Z0-9]/', '', $string);

        // make uppercase
        if ($toLower && $toUpper) {
            throw new Exception('You cannot set both options (uppercase|lowercase) to "true"!');
        } else if ($toLower) {
            return strtolower($striped);
        } else if ($toUpper) {
            return strtoupper($striped);
        } else {
            return $striped;
        }
    }

    /**
     * Constantize a string
     *
     * @param $string
     * */
    public function constantize($string)
    {
        return $string ? strtoupper(str_replace(" ", "-", trim($string))) : "";
    }

    /**
     * @param $string
     *
     * @return string
     */
    public static function seoFriendlyUrl($string)
    {
        $string = str_replace(['[\', \']'], '', $string);
        $string = preg_replace('/\[.*\]/U', '', $string);
        $string = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', '-', $string);
        $string = htmlentities($string, ENT_COMPAT, 'utf-8');
        $string = preg_replace(
            '/&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);/i', '\\1', $string
        );
        $string = preg_replace(['/[^a-z0-9]/i', '/[-]+/'], '-', $string);

        return strtolower(trim($string, '-'));
    }

    public static function timeJsToMysqlTime($strTime='')
    {
        return $strTime ? 
            (count($timeFormatLength = explode(':', $strTime)) > 2 ? $strTime : $strTime . ":00") : 
            ''; 
    }

    /**
     * @param $_month: Integer
     * @param $_year: Integer
     */
    public static function getWeekOfMonth($_month, $_year=null){
        $result = array();
        $_year = $_year ? $_year : date('Y');
        $initWeek = 0;
        for($d=1; $d<=31; $d++)
        {
            $time=mktime(12, 0, 0, $_month, $d, $_year);          
            $a = date('Y-m-d', $time);
            $b = new \DateTime($a);
            if(
                $initWeek != $b->format('W')
            ){
                array_push($result, [$b->format('d M'), $b->format('W')]);
            }                   
            $initWeek = $b->format('W');
        }

        return $result;
    }
}