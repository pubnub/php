<?php
 
namespace Pubnub;

class PubnubUtil
{
    /**
     * Encode string value
     *
     * @param string $value
     * @return string
     */
    public static function url_encode($value)
    {
        return rawurlencode($value);
    }

    /**
     * Decode JSON value
     *
     * @param string $val
     * @param bool $assoc
     * @param int $depth
     * @return mixed
     */
    public static function json_decode($val, $assoc = true, $depth = 512)
    {

        return json_decode($val, $assoc, $depth);

    }

    /**
     * Encode JSON value
     *
     * @param $val
     * @return string
     */
    public static function json_encode($val)
    {
        return json_encode($val);
    }

    /**
     * Tests if given string ends with the specified suffix.
     *
     * @param string $string
     * @param string $suffix
     * @return bool
     */
    public static function string_ends_with($string, $suffix)
    {
        $str_len = strlen($string);
        $suffix_len = strlen($suffix);

        if ($suffix_len > $str_len) return false;

        return substr_compare($string, $suffix, $str_len - $suffix_len, $suffix_len) === 0;
    }
}