<?php
/**
 * Created by PhpStorm.
 * User: Terry
 * Date: 7/5/2017
 * Time: 6:36 AM
 */

namespace Tops\sys;


class TStrings
{

    const initialCapFormat = 1;
    const wordCapsFormat = 2;
    const keyFormat = 3;
    const dashedFormat = 4;
    const camelCaseFormat = 5;
    const pascalCaseFormat = 6;
    const wordFormat = 7;

    public static function ListToArray($value, $seperator = ',')
    {
        if (empty($value)) {
            return array();
        }
        $parts = explode($seperator, $value);
        return array_map('trim', $parts);
    }

    public static function ConvertNameFormat($name, $format, $uppercase = false)
    {
        $parts = [];
        if ($name == null) {
            return false;
        }
        $name = trim($name);
        if ($name == '') {
            return '';
        }
        $singleWord = false;
        if (strpos($name, '-') !== false) {
            $parts = explode('-', strtolower($name));
        } else if (strpos($name, '_') !== false) {
            $parts = explode('_', strtolower($name));
        } else if (strpos($name, ' ') !== false) {
            $parts = explode(' ', strtolower($name));
        } else {
            $singleWord = (lcfirst($name) == strtolower($name));
            if (!$singleWord) {
                $parts = self::camelCaseExplode($name);
            }
        }

        $glue = '';
        switch ($format) {
            case self::initialCapFormat :
                if ($singleWord) {
                    return $uppercase ? strtoupper($name) : ucfirst(strtolower($name));
                }
                $glue = ' ';
                if (!$uppercase) {
                    $parts[0] = ucfirst($parts[0]);
                }
                break;
            case self::wordCapsFormat :
                if ($singleWord) {
                    return $uppercase ? strtoupper($name) : ucfirst(strtolower($name));
                }
                $glue = ' ';
                if (!$uppercase) {
                    $len = sizeof($parts);
                    for ($i = 0; $i < $len; $i++) {
                        $parts[$i] = ucfirst($parts[$i]);
                    }
                }
                break;
            case self::wordFormat :
                if ($singleWord) {
                    return $uppercase ? strtoupper($name) : strtolower($name);
                }
                $glue = ' ';
                break;
            case self::keyFormat :
                if ($singleWord) {
                    return $uppercase ? strtoupper($name) : strtolower($name);
                }
                $glue = '_';
                break;
            case self::dashedFormat :
                if ($singleWord) {
                    return $uppercase ? strtoupper($name) : strtolower($name);
                }
                $glue = '-';
                break;
            case self::camelCaseFormat :
                if ($singleWord) {
                    return $uppercase ? strtoupper($name) : strtolower($name);
                }
                $len = sizeof($parts);
                if ($len > 1 && !$uppercase) {
                    for ($i = 1; $i < $len; $i++) {
                        $parts[$i] = ucfirst($parts[$i]);
                    }
                }
                break;
            case self::pascalCaseFormat :
                if ($singleWord) {
                    return $uppercase ? strtoupper($name) : ucfirst(strtolower($name));
                }
                if (!$uppercase) {
                    $len = sizeof($parts);
                    for ($i = 0; $i < $len; $i++) {
                        $parts[$i] = ucfirst($parts[$i]);
                    }
                }
                break;
        }
        $result = join($glue, $parts);
        if ($uppercase) {
            $result = strtoupper($result);
        }
        return $result;
    }

    public static function toCamelCase($s, $delimiter = '-')
    {
        $parts = explode($delimiter, $s);
        $len = sizeof($parts);
        for ($i = 0; $i < $len; $i++) {
            $parts[$i] = ucfirst($parts[$i]);
        }
        return join('', $parts);
    }

    public static function toTitle($s, $seperator = false)
    {
        if ($seperator) {
            $s = str_replace($seperator, ' ', $s);
        }
        $parts = explode(' ', $s);
        $len = sizeof($parts);
        $keywords = explode(',', TLanguage::text('title-key-words', 'the,a,of,an,in,and'));
        for ($i = 0; $i < $len; $i++) {
            $part = $parts[$i];
            if ($i > 0 && in_array($part, $keywords)) {
                continue;
            }
            $parts[$i] = ucfirst($part);
        }
        return join(' ', $parts);
    }

    /**
     * Convert namespace code, usually for a service call to literal namespace, per TOPS convention
     * Periods replaed by backslashes
     * First part converts to Pascal case
     * Subsequent parts convert to Pascal case if it contains a hyphen, otherwise literal text used.
     *
     * example:
     * two-quakers.testing.services.sub-services > TwoQuakers\testing\services\SubServices
     * two-quakers.Testing.services.sub-services > TwoQuakers\Testing\services\SubServices
     *
     * @param $nscode string
     * @return bool|string
     */
    public static function formatNamespace($nscode)
    {
        if (empty($nscode)) {
            return false;
        }
        $parts = explode('.', $nscode);

        $count = sizeof($parts);
        for ($i = 0; $i < $count; $i++) {
            $part = $parts[$i];
            if ($i == 0 || strpos($part, '-') !== false) {
                $parts[$i] = self::toCamelCase($part);
            }
        }
        return join('\\', $parts);
    }

    /**
     * thanks a lot Charl van Niekerk, http:/ /blog.charlvn.za.net/2007/11/php-camelcase-explode-20.html
     *
     * @param $string :string
     * The original string, that we want to explode.
     *
     * @param $lowercase :boolean
     * should the result be lowercased?
     *
     * @param $example_string :string
     *         Example to specify how to deal with multiple uppercase characters.
     * Can be something like "AA Bc" or "A A Bc" or "AABc".
     *
     * @param $glue :boolean
     * Allows to implode the fragments with sth like "_" or "." or " ".
     *     If $glue is FALSE, it will just return an array.
     *
     * @return :array[int => string] or just string, depending on $glue.
     */
    public static function camelCaseExplode($string, $lowercase = true, $example_string = 'AA Bc', $glue = false)
    {
        static $regexp_available = array(
            '/([A-Z][^A-Z]+)/',
            '/([A-Z]+[^A-Z]*)/',
            '/([A-Z][^A-Z]*)/',
        );
        static $regexp_by_example = array();
        if (!isset($regexp_by_example[$example_string])) {
            $example_array = explode(' ', $example_string);
            foreach ($regexp_available as $regexp) {
                if (implode(' ', preg_split(
                        $regexp,
                        str_replace(' ', '', $example_string),
                        -1,
                        PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
                    )) == $example_string) {
                    break;
                }
            }
            $regexp_by_example[$example_string] = $regexp;
        }
        $array = preg_split(
            $regexp_by_example[$example_string],
            $string,
            -1,
            PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
        );
        if ($lowercase) $array = array_map('strtolower', $array);
        return is_string($glue) ? implode($glue, $array) : $array;
    }

    public static function getTeaser($text, $length = 100)
    {
        if (strlen($text) <= $length) {
            return $text;
        }

        $text = strip_tags($text);
        $text = str_replace(["\n", "\t"], ' ', trim($text));
        $cleaned = '';
        while ($text != $cleaned) {
            $cleaned = $text;
            $text = str_replace('  ', ' ', $text);
        }

        $words = explode(' ', $text);
        $total = 0;
        $result = [];
        foreach ($words as $word) {
            $total += (strlen($word) + 1);
            $result[] = $word;
            if ($total >= $length) {
                break;
            }
        }
        return implode(' ', $result) . '...';
    }

    /**
     * Trim leading punctuation and whitespace and trailing whitespace only
     *
     * @param $s
     * @return string
     */
    public static function TrimString($s): string
    {
        $s = self::TrimStart($s);
        return trim($s);
    }

    public static function StripPunctuation($s,$tolower=true): string {
        if ($s===null) return '';
        $result = preg_replace('/[[:punct:]]+/', '', $s);
        return $tolower ? strtolower($result) : $result;
    }

    public static function IsEmpty($s): string {
        return $s===null || trim($s) === '';
    }

    public static function TrimPunctuation($s): string {
        if ($s===null) return '';
        $s = self::TrimStart($s);
        return self::TrimEnd($s);
    }
    public static function TrimStart(string $s) : string
    {
        if ($s === null) {
            return '';
        }
        // Remove leading punctuation/whitespace
        return preg_replace('/^[\p{P}\p{Z}\s]+/u', '', $s);
    }

    public static function TrimEnd(string $s)  : string
    {
        if ($s === null) {
            return '';
        }
        // Remove trailing punctuation/whitespace
        return preg_replace('/[\p{P}\p{Z}\s]+$/u', '', $s);
    }

    /**
     * Remove common title prefixes/suffixes from a name string,
     * but preserve middle initials and other legitimate periods.
     */
    public static function stripTitles(string $name): string
    {
        if ($name === null) {
            return '';
        }
        // Titles to remove (prefixes and suffixes)
        $titles = [
            'mr','mrs','ms','miss','mx',
            'dr','prof','rev','fr',
            'sr','sra','jr',
            'ii','iii','iv'
        ];

        // Build regex that matches titles with optional trailing period
        // Example: \b(?:mr\.?|mrs\.?|dr\.?)\b
        $parts = array_map(fn($t) => $t . '\.?', $titles);
        $pattern = '/\b(?:' . implode('|', $parts) . ')\b/i';

        // Remove titles but leave other periods intact
        $clean = preg_replace($pattern, '', $name);

        // Collapse extra whitespace
        $clean = preg_replace('/\s+/u', ' ', $clean);
        // Remove leading punctuation/whitespace
        $clean = preg_replace('/^[\p{P}\p{Z}\s]+/u', '', $clean);
        // Remove trailing punctuation/whitespace
        return preg_replace('/[\p{P}\p{Z}\s]+$/u', '', $clean);
    }


    public static function SplitName(string $fullName)
    {
        $result = new \stdClass();
        $result->lastName = '';
        $result->firstName = '';
        $result->middleName = '';
        $simpleName = self::stripTitles($fullName);
        if ($simpleName !== '') {
            $parts = explode(' ', $simpleName);
            $result->lastName = array_pop($parts);
            $count = count($parts);
            if ($count > 0) {
                $result->firstName = array_shift($parts);
            }
            if ($count > 0) {
                $result->middleName = implode(' ', $parts);
            }
        }
        return $result;
    }

    public static function ConcatName(string $first, string $last, string $middle = null)
    {
        $a = [];
        if (!empty($first)) {
            $a[] = trim($first);
        }
        if (!empty($last)) {
            $a[] = trim($last);
        }
        if (!empty($middle)) {
            $a[] = trim($middle);
        }
        return implode(' ', $a);
    }

}