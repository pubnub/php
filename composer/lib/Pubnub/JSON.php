<?php
 
namespace Pubnub;

class JSON
{
    /**
     * Decode value
     *
     * @param string $val
     * @param bool $assoc
     * @param int $depth
     * @return mixed
     */
    public static function decode($val, $assoc = true, $depth = 512)
    {

        return json_decode($val, $assoc, $depth);

    }

    /**
     * Encode value
     *
     * @param $val
     * @return string
     */
    public static function encode($val)
    {
        return json_encode($val);
    }
}