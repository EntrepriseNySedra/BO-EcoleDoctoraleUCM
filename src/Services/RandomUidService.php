<?php

namespace App\Services;

/**
 * Description of RandomUidService.php.
 *
 * @package App\Services
 */
class RandomUidService
{

    const MIN_LENGTH = 6;

    /**
     * MASK FORMAT [XXX-XXX]
     * 'X' this is random symbols
     * '-' this is separator
     *
     * @param array $options
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
            for ($i = 0;$i < strlen($mask);$i++) {
                if ($mask[$i] === 'X') {
                    $coupon .= $characters[mt_rand(0, count($characters) - 1)];
                } else {
                    $coupon .= $mask[$i];
                }
            }
        } else {
            for ($i = 0;$i < $length;$i++) {
                $coupon .= $characters[mt_rand(0, count($characters) - 1)];
            }
        }

        return $prefix.$coupon.$suffix;
    }

    /**
     * @param int   $length
     * @return array
     */
    public static function generateUid($length)
    {
        $options['length'] = $length;
        $options['numbers'] = true;

        return strtolower(self::generate($options));
    }

    /**
     * Strip all characters but letters and numbers
     *
     * @param       $string
     * @param array $options
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
}