<?php

namespace Sinergia\Ofx;

/**
 * Convert Ofx string to XML
 *
 * Class OfxParser
 * @package Sinergia\Ofx
 */
class OfxParser
{
    public static function parse($str)
    {
        return strpos($str, "\n") ? static::parseString($str)
                                  : static::parseFile($str);
    }

    public static function parseString($string)
    {
        list($header, $body) = static::splitHeaderBody($string);
        $headers = static::parseHeader($header);
        $body = static::fixBody($body);
        $xml = static::bodyAsXml($body);

        return array($headers, $xml);
    }

    public static function parseFile($file)
    {
        return static::parseString(file_get_contents($file));
    }

    protected static function splitHeaderBody($string)
    {
        list($header, $body) = explode("<OFX>", $string, 2);
        $header = trim($header);
        $body = trim("<OFX>$body");

        return array($header, $body);
    }

    protected static function parseHeader($header)
    {
        $lines = preg_split("!\r\n|\r|\n!", trim($header));
        $headers = array();

        foreach ($lines as $line) {
            list($k, $v) = explode(":", $line, 2);
            $headers[$k] = $v;
        }

        return $headers;
    }

    /**
     * Close the tag only when needed
     * @param $body string
     * @return string
     */
    protected static function fixBody($body)
    {
        // m modifier does not match \r, so remove all of them
        $body = preg_replace("!\r!", "", $body);
        // add closing tag just when needed: <CODE>0 but not <STATUS> or when already has: <CODE>0</CODE>
        return preg_replace("!^<([^/>]+)>([^<>]+)$!m", '<$1>$2</$1>', $body);
    }

    protected static function bodyAsXml($body)
    {
        if ( mb_detect_encoding($body) == 'UTF-8' ) {
            $body = utf8_encode($body);
        }

        libxml_use_internal_errors(true);
        $sxml = simplexml_load_string($body);
        if ($sxml === false) {
            static::handleXmlErrors($body);
        }
        libxml_use_internal_errors(false);

        return $sxml;
    }

    protected static function handleXmlErrors($body)
    {
        $errors = array();
        foreach (libxml_get_errors() as $error) {
            $error->file = $body;
            $errors[] = $error;
        }
        libxml_clear_errors();
        libxml_use_internal_errors(false);
        throw new \RuntimeException($errors[0]->message, $errors[0]->code);
    }
}
