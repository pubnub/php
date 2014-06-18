<?php

class JSON
{
    /**
     * Decode value
     *
     * @param string $val
     * @param bool $assoc
     * @param int $depth
     * @param int $options
     * @return mixed
     */
    public static function decode($val, $assoc = true, $depth = 512, $options = 0)
    {
 
        return json_decode($val, $assoc);


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