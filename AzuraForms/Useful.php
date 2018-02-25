<?php
namespace AzuraForms;

class Useful
{
    /**
     * Strip out all empty characters from a string
     *
     * @param string $val
     * @return string
     */
    public static function stripper($val)
    {
        foreach (array(' ', '&nbsp;', '\n', '\t', '\r') as $strip) {
            $val = str_replace($strip, '', (string) $val);
        }

        return $val === '' ? false : $val;
    }

    /**
     * Slugify a string using a specified replacement for empty characters
     *
     * @param string $text
     * @param string $replacement
     * @return string
     */
    public static function slugify($text, $replacement = '-')
    {
        return strtolower(trim(preg_replace('/\W+/', $replacement, $text), '-'));
    }

    /**
     * Return a random string of specified length
     *
     * @param int $length
     * @param string $return
     *
     * @return string
     * @throws \Exception
     */
    public static function randomString($length = 10, $return = '')
    {
        $string = 'qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM1234567890';
        while ($length-- > 0){
            $return .= $string[random_int(0, strlen($string) - 1)];
        }

        return $return;
    }

    /**
     * Return a UTC-localized time code given a HTML5 time input's return value.
     *
     * @param $input_time
     * @return int
     */
    public static function getTimeCode($input_time): int
    {
        $dt = \DateTime::createFromFormat('!G:i', $input_time);
        $dt->setTimezone(new \DateTimeZone('UTC'));

        return (int)$dt->format('Gi');
    }

    /**
     * Get a Unix timestamp from a given date from an HTML5 date input.
     *
     * @param $input_date
     * @return int
     */
    public static function getTimestampFromDate($input_date): int
    {
        $dt = \DateTime::createFromFormat('!Y-m-d', $input_date, new \DateTimeZone('UTC'));
        return (int)$dt->getTimestamp();
    }
}
