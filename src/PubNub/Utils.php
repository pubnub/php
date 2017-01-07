<?php

namespace PubNub;


class Utils
{
    public static function buildUrl($scheme, $origin, $path, $params)
    {
        return $scheme . $origin . $path . http_build_query($params);
    }
}