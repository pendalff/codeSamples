<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Russian declension of numerals
 * @author Sem
 * @email yapendalff@gmail.com
 * @url http://pendalff.ru
 */
class Core_Num  {

    public static $words = array('комментарий', 'комментария', 'комментариев');

    public static function decl($number , $array = FALSE)
    {
        if($array == FALSE)
            $array = self::$words;

        $cases = array (2, 0, 1, 1, 1, 2);

        if ($number % 100 > 4 AND $number % 100 < 20)
        {
            return $number.' '.__ ($array[2]);
        }
        else
        {
            return $number.' '.__ ($array[$cases[min($number % 10, 5)]]);
        }
    }

    public static function decl_word($number , $array = FALSE)
    {
        if($array == FALSE)
            $array = self::$words;

        $cases = array (2, 0, 1, 1, 1, 2);

        if ($number % 100 > 4 AND $number % 100 < 20)
        {
            return __ ($array[2]);
        }
        else
        {
            return __ ($array[$cases[min($number % 10, 5)]]);
        }
    }

		
	public static function translit($string)
	{
	    $converter = array(
	        'а' => 'a',   'б' => 'b',   'в' => 'v',
	        'г' => 'g',   'д' => 'd',   'е' => 'e',
	        'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
	        'и' => 'i',   'й' => 'y',   'к' => 'k',
	        'л' => 'l',   'м' => 'm',   'н' => 'n',
	        'о' => 'o',   'п' => 'p',   'р' => 'r',
	        'с' => 's',   'т' => 't',   'у' => 'u',
	        'ф' => 'f',   'х' => 'h',   'ц' => 'c',
	        'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
	        'ь' => "_",  'ы' => 'y',   'ъ' => "_",
	        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
	
	        'А' => 'A',   'Б' => 'B',   'В' => 'V',
	        'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
	        'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
	        'И' => 'I',   'Й' => 'Y',   'К' => 'K',
	        'Л' => 'L',   'М' => 'M',   'Н' => 'N',
	        'О' => 'O',   'П' => 'P',   'Р' => 'R',
	        'С' => 'S',   'Т' => 'T',   'У' => 'U',
	        'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
	        'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
	        'Ь' => "_",  'Ы' => 'Y',   'Ъ' => "_",
	        'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
			'/' => '_',   ' ' => '_',  '.'  => '_',
			',' => '_',   ';' => '_',  ':'  => '_'
	    );
	    return strtr($string, $converter);
	}

}


?>