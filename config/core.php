<?php 
use Cake\Core\Configure;
use Cake\Utility\Inflector;
use Cake\Core\Exception\Exception;
use Cake\I18n\Time;
use Cake\I18n\Date;

if (!function_exists('is_natural')) {

    /**
     * is_natural method
     * checks if the given number is a natural number
     *
     * @param int|float|array $number            
     * @param bool $zero - if set to true zero will be considered
     * @return bool
     */
    function is_natural($number, $zero = false)
    {
        if (! is_array($number)) {
            $number = [
                $number
            ];
        }
        
        $s = false;
        foreach ($number as $n) {
            $n = (string) $n;
            if (ctype_digit($n) && ($zero ? $n >= 0 : $n > 0)) {
				$s = true;
				continue;
			}
			
			$s = false;
			break;
		}
		
		return $s;
	}
}


if (!function_exists('between')) {
/**
 * between method
 * checks if the given value is between 2 values(inclusive)
 *
 * @param int $number
 * @param int $min
 * @param int $max
 * @return bool
 */
	function between($number, $min, $max)
	{
		if ($number >= $min && $number <= $max) {
			return true;
		}
		
		return false;
	}
}

if (!function_exists('lastChars')) {
	/**
	 * lastChars method
	 * returns given amount of last characters
	 *
	 * @param string $str
	 * @param int $count
	 * @return string
	 */
	function lastChars($str, $count = 1)
	{
		return mb_substr($str, -$count, $count);
	}

}


if (!function_exists('createSlug')) {
/**
 * createSlug method - creates slug from string
 *  
 * @param string $str
 * @param string $symbol
 * @return string - in word1-word2-word3 format
 */
	function createSlug($str = "", $symbol = "-")
	{
		// if not english
		$regex = '/^[ -~]+$/';
		if (!preg_match($regex, $str)) {
			$str = transliterator_transliterate('Any-Latin;Latin-ASCII;', $str);
		}
		
		$str = mb_strtolower($str);
		$str = str_replace("'", "", $str);
		$str = str_replace('"', "", $str);
		$str = str_replace(".", $symbol, $str);
		$str = str_replace("\\", $symbol, $str);
		$str = str_replace("/", $symbol, $str);
		$str = preg_replace("/[~\:;\,\?\s\(\)\'\"\[\]\{\}#@&%\$\!\^\+\*=\!\<\>\|´`]/", $symbol, trim($str));
	
		// everything but letters and numbers
		$str = preg_replace('/(.)\\1{2,}/', '$1', $str);

		// letters replace only with 2+ repetition
		$str = preg_replace("/[-]{2,}/", $symbol, $str);
		$str = rtrim($str, $symbol);
	
		return mb_strtolower($str);
	}
}

if (!function_exists('emptySet')) {
/**
 * emptySet method
 * returns messge wrapped in div with corresponding class
 *  
 * @param string $str
 * @param string $html - html code to include
 * @return string
 */
	function emptySet($str, $html = '')
	{
		return '<div class="empty-set">'.$html.h($str).'</div>';
	}
}


if (!function_exists('generateRandomStr')) {
/**
 * generateRandomStr method - generates random string for a given length
 *
 * @param int $length - the length of the string, max value 100
 * @param bool $alphanumeric - by default is true, only letters and numbers are used, 
 *       if false all signs will be included as well
 * @param bool $hash - default false, if set to true hash will be applied to the final string
 * @return string
 */
	function generateRandomStr($length = 10, $alphanumeric = true, $hash = false)
	{
		$maxLength = $alphanumeric ? 50 : 80;

		if ($length > $maxLength) {
			throw new Exception('Max length is '.$maxLength);
		}

		$string = "";
		$i = 0;
		$possible = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$signs = "!#$%&()*+,-./:;<=>?@[]^_`{|}~";

		if (!$alphanumeric) {
			$possible .= $signs;
		}
	
		$possibleLength = strlen($possible)-1;
	
		while ($i < $length){
			$char = substr($possible, mt_rand(0, $possibleLength), 1);
			if (!strstr($string, $char)) {
				$string .= $char;
				$i++;
			}
		}

		if ($hash) {
			if (is_string($hash) && in_array($hash, hash_algos())) {
				$string = hash($hash, $string);
			} else {
				$string = hash('sha512', $string);
			}
		}
	
		return $string;
	}
}


if (!function_exists('getRandomStr')) {

    /**
     * getRandomStr method
     * get random string using /dev/urandom
     *
     * @link http://security.stackexchange.com/a/3939/38200
     *      
     * @param number $length            
     * @param string|bool $hash
     * @return string
     */
    function getRandomStr($length = null, $hash = false)
    {
        if (! $length) {
            $length = 50;
        }
        
        $fp = @fopen('/dev/urandom', 'rb');
        if ($fp === false) {
            throw new Exception('Can not use urandom');
        }
        
        $pr_bits = @fread($fp, $length);
        @fclose($fp);
        
        if (! $pr_bits) {
            throw new Exception('Unable to read from urandom');
        }
        
        if ($hash) {
            if (is_string($hash) && in_array($hash, hash_algos())) {
                $string = hash($hash, $pr_bits);
            } else {
                $string = hash('sha512', $pr_bits);
            }
            
            return $string;
        }
        
        return substr(base64_encode($pr_bits), 0, $length);
    }
}

if (!function_exists('getUniqueToken')) {
    /**
     * getUniqueToken method
     * wrapper for getRandomStr
     *
     * @param bool $long
     * @return string
     */
    function getUniqueToken($long = true)
    {
        return getRandomStr(300, $long ? 'sha512' : 'sha256');
    }
}


if (! function_exists('coalesce')) {

    /**
     * coalesce method
     *
     * @param
     *            mixed - list of arguments
     * @return mixed
     * @link http://stackoverflow.com/a/4688108/932473
     */
    function coalesce()
    {
        $res = array_filter(func_get_args());
        return array_shift($res);
    }
}


if (! function_exists('getFirstKey')) {

    /**
     * getFirstKey method
     * returns the first key of the array
     *
     * @param array $array            
     * @return mixed
     */
    function getFirstKey(array $array = [])
    {
        if (! is_array($array)) {
            return false;
        }
        reset($array);
        return key($array);
    }
}

if (! function_exists('getFirstValue')) {

    /**
     * getFirstValue method
     * returns the first value of the array
     *
     * @param array $array            
     * @return mixed
     */
    function getFirstValue($array)
    {
        if (! is_array($array)) {
            return false;
        }
        return reset($array);
    }
}

if (! function_exists('getLastKey')) {

    /**
     * getLastKey method
     * returns the last key of the array
     *
     * @param array $array            
     * @return mixed
     */
    function getLastKey($array)
    {
        if (! is_array($array)) {
            return false;
        }
        $array = array_reverse($array, true);
        reset($array);
        return key($array);
    }
}


if (!function_exists('getLastValue')) {

    /**
     * getLastValue method
     * returns the last value of the array
     *
     * @param array $array            
     * @return mixed
     */
    function getLastValue($array)
    {
        if (! is_array($array)) {
            return false;
        }
        $array = array_reverse($array);
        return reset($array);
    }
}

if (!function_exists('array_unset')) {
/**
 * array_unset method - unsets array items by value
 *
 * @param array $array - the original array
 * @param string|array - the value or array of values to be usnet
 * @return array - the processed array
 */
    function array_unset($array, $values = [])
    {
        if (is_string($values)) {
            $values = [$values];
        }
        
        return array_diff($array, $values);
    }
}

if (!function_exists('array_iunique')) {
    /**
     * array_iunique method
     * case-insensitive array_unique
     *
     * @param array
     * @return array
     * @link http://stackoverflow.com/a/2276400/932473
     */
    function array_iunique($array)
    {
        $lowered = array_map('mb_strtolower', $array);
        return array_intersect_key($array, array_unique($lowered));
    }
}


if (!function_exists('is_numeric_list')) {
	/**
	 * is_numeric_list method -
	 * if an array is provided checks the values of the array all to be numeric,
	 * if string is provided, will check to be comma separated list
	 *
	 * @param mixed $data - array of numbers or string as comma separated numbers
	 * @return bool
	 */
	function is_numeric_list($data)
	{
		if (is_array($data)) {
			$data = implode(",", $data);
		}

		return preg_match('/^([0-9]+,)*[0-9]+$/', $data);
	}
}



if (!function_exists('getDirectorySize')) {
	/**
	 * getDirectorySize method
	 * returns the size of the directory
	 * 
	 * @param string $path
	 * @param string $type
	 * @param string $intOnly
	 * @throws Exception
	 * @return string
	 * @link http://stackoverflow.com/a/478161/932473
	 */
	function getDirectorySize($path = null, $type = false, $intOnly = false)
	{
		if (!$path || !is_dir($path)) {
			throw new Exception(__('Invalid directory'));
		}

		$io = popen('/usr/bin/du -sk ' . $path, 'r');
		$size = fgets($io, 4096);
		$size = substr($size, 0, strpos($size, "\t"));
		pclose($io);
		
		if ($type) {
			$type = strtoupper($type);
			$typeStr = $intOnly ? '' : ' '.$type;
			
			if ($type == 'B') {
				return ($size*1024).$typeStr;
			} elseif ($type == 'MB') {
				return round($size/1024, 1).$typeStr;
			} elseif ($type == 'GB') {
				return round($size/(1024*1024), 1).$typeStr;
			} else {
				return $size.$typeStr;
			}
		}

		if ($size < 1000) {
			return $size.' KB';
		} elseif ($size < 999999) {
			return round(($size/1024), 1).' MB';
		} else {
			return round(($size/(1024*1024)), 1).' GB';
		}
		
	}

}

if (!function_exists('getFileName')) {
	/**
	 * getFileName method
	 * returns the file name without the extension
	 * 
	 * @param string $fileName
	 * @return string
	 */
	function getFileName($fileName = '')
	{
		$arr = explode(".", $fileName);
		array_pop($arr);
		return implode(".", $arr);
	}
}

if (!function_exists('getFileExtension')) {
	/**
	 * getFileExtension method
	 * returns the file extension from full file name
	 * 
	 * @param string $fileName
	 * @return string
	 */
	function getFileExtension($fileName)
	{
		return substr(strrchr($fileName, '.'), 1);
	}
}

if (!function_exists('checkFileExists')) {
	/**
	 * checkFileExists method
	 * if file exists will return it - with number concatenated
	 * 
	 * @param string $path
	 * @param string $fileName
	 * @param number $n
	 * @return string|bool
	 */
	function checkFileExists($path, $fileName, $n = 100)
	{
		// just in case
		$path = rtrim($path, DS) . DS;

		if (!file_exists($path . $fileName)) {
			return $fileName;
		}

		$name = getFileName($fileName);
		$ext = getFileExtension($fileName);

		$i = 1;
		$status = true;
		while (file_exists($path . $fileName)) {
			$fileName = $name . '_' . $i . '.' . $ext;
			$i++;

			if ($i > $n) {
				$status = false;
				break;
			}
		}

		if ($status) {
			return $fileName;
		}

		return false;
	}
}

if (!function_exists('formatBytes')) {
	/**
	 * formatBytes method
	 * 
	 * @param string $bytes
	 * @param number $precision
	 * @return string
	 * @link http://stackoverflow.com/a/2510459/932473
	 */
	function formatBytes($bytes, $precision = 0)
	{
		$units = ['B', 'KB', 'MB', 'GB', 'TB'];

		$bytes = max($bytes, 0);
		$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
		$pow = min($pow, count($units) - 1);

		$bytes /= pow(1024, $pow);

		return round($bytes, $precision) . ' ' . $units[$pow];
	}
}


if (!function_exists('datetotime')) {
	/**
	 * datetotime - return timestamp from date considering "/" delimiter
	 * @return string
	 */
	function datetotime($date)
	{
		return strtotime(str_replace("/", "-", $date));
	}
}


if (!function_exists('isDate')) {
	/**
	 * isDate method
	 * checks if the given date(s) are valid mysql dates
	 *
	 * @param date $date1
	 * @param date $date [optional]
	 * @return boolen - true if all dates are valid, false otherwise
	 */
	function isDate($date1 = null)
	{
		if (!$date1 || $date1 == '1970-01-01') {
			return false;
		}
		$status = true;
		for($i = 0 ; $i < func_num_args(); $i++) {
			$res = date_parse(func_get_arg($i));
			if ($res['year'] && $res['year'] != '1970' && $res['month'] && $res['day'] &&
					$res['warning_count'] == 0 && $res['error_count'] == 0) {
						continue;
					}
						
					$status = false;
					break;
		}

		return $status;
	}

}



if (!function_exists('t2d')) {
/**
 * t2d method
 * returns date in Y-m-d format from seconds
 * 
 * @param time $timeStr
 * @return date
 */
	function t2d($timeStr = null)
	{
		$timeStr = $timeStr ? $timeStr : time();
		return date("Y-m-d", $timeStr);		
	}
}

if (!function_exists('t2dt')) {
/**
 * t2dt method
 * returns date in Y-m-d H:i:s format from seconds
 * 
 * @param time $timeStr
 * @return datetime
 */
	function t2dt($timeStr = null)
	{
		$timeStr = $timeStr ? $timeStr : time();
		return date("Y-m-d H:i:s", $timeStr);		
	}
}



if (!function_exists('getUserDate')) {

    /**
     * getUserDate method
     * returns date - based on user's timezone
     *
     * @param sting|Cake\I18n\Time|Cake\I18n\Date $date            
     * @param bool|string $raw
     *            - if true, only date will be returned in the given format,
     *            if string as 'db', result will be in "Y-m-d" format
     *            if string as 'obj', Cake\I18n\Date object will be returned
     * @param bool $time
     *            - if true and $raw is false, when returning as html, time will be included as title
     * @return string|Cake\I18n\Date
     */
    function getUserDate($date, $raw = false, $time = true)
    {
        if (! isDate($date)) {
            return $date;
        }

        $serverTimezone = Configure::read('CakeTools.settings.server_timezone');
        $userTimezone = Configure::read('CakeTools.settings.user_timezone');

        if (is_string($date)) {
            if (strlen($date) > 10) {
                $date = Time::createFromFormat('Y-m-d H:i:s', $date, $serverTimezone);
            } else {
                $date = Time::createFromFormat('Y-m-d', $date, $serverTimezone);
            }
        }
        
        $origDate = $date;
        if (is_a($date, 'Cake\I18n\Time') || is_a($date, 'Cake\I18n\FrozenTime')) {
            $date = Date::createFromTimestamp($date->toUnixString(), $date->getTimezone());
        }
        
        if (is_a($date, 'Cake\I18n\Date') || is_a($date, 'Cake\I18n\FrozenDate') ) {
            if ($time) {
                $dt = $origDate->i18nFormat('YYY-MM-dd HH:mm:ss', $userTimezone);
            } else {
                $dt = $date->i18nFormat('YYY-MM-dd', $userTimezone);
            }
            
            if ($raw === 'db') {
                return $date->i18nFormat('YYY-MM-dd', $userTimezone);
            } elseif ($raw === 'obj') {
                $date->setTimezone($userTimezone);
                return $date;
            } elseif ($raw === true) {
                return $date->nice($userTimezone);
            } else {
                return '<span title="'.$dt.'">'.$date->nice($userTimezone).'</span>';
            }
        }
        
        return '';
    }
}

if (!function_exists('getUserDateTime')) {

    /**
     * getUserDateTime method
     * returns the date and time, based on user's time zone
     *
     * @param string|Cake\I18n\Time $date            
     * @param bool|string $raw            
     * @return string|Cake\I18n\Time $date
     */
    function getUserDateTime($date, $raw = false)
    {
        if (! isDate($date)) {
            return $date;
        }
        
        $serverTimezone = Configure::read('CakeTools.settings.server_timezone');
        $userTimezone = Configure::read('CakeTools.settings.user_timezone');

        if (is_string($date)) {
            $date = Time::createFromFormat('Y-m-d H:i:s', $date, $serverTimezone);
        }
        
        if (is_a($date, 'Cake\I18n\Time') || is_a($date, 'Cake\I18n\FrozenTime')) {
            $dt = $date->i18nFormat('YYY-MM-dd HH:mm:ss', $userTimezone);
            
            if ($raw === 'db') {
                return $dt;
            } elseif ($raw === 'obj') {
                $date->setTimezone($userTimezone);
                return $date;
            } elseif ($raw === true) {
                return $date->nice($userTimezone);
            } else {
                return '<span title="'.$dt.'">'.$date->nice($userTimezone).'</span>';
            }
        }
        
        return '';
    }
}

if (!function_exists('getRange')) {

    /**
     * getRange method
     * returns array with options for select box
     *
     * @return array
     */
    function getRange($min, $max, $step = 1)
    {
        return array_combine(range($min, $max, $step), range($min, $max, $step));
    }

}


if (!function_exists('getClassConstant')) {
	/**
	 * getClassConstant method
	 * returns constant of the class based on its value
	 *
	 * @return array
	 */

	function getClassConstant($className, $value)
	{
		if (class_exists($className)) {
			$reflection = new ReflectionClass($className);
			return Inflector::humanize(Inflector::underscore(trim(array_search($value, $reflection->getConstants()), '_')));
		}

		throw new Exception($className.' class does not exist');
	}

}

if (!function_exists('getClassConstants')) {
	/**
	 * getConstants method
	 * returns constans of the class
	 *
	 * @return array
	 */

	function getClassConstants($className, $reverse = false)
	{
		if (!class_exists($className)) {
			throw new Exception($className.' class does not exist');
		}

		$refl = new ReflectionClass($className);
		$constants = $refl->getConstants();

		if ($reverse) {
			$constants = array_flip($constants);
				
			array_walk($constants, function(&$val, $k) {
				$val = __(Inflector::humanize(Inflector::underscore($val)));
			});
		}

		return $constants;
	}

}


if (!function_exists('yesNo')) {
/**
 * yesNo method
 * returns Yes or No
 *
 * @param bool $type
 * @return string
 */
	function yesNo($type)
	{
		return $type ? __('Yes') : __('No');
	}
}

if (!function_exists('shorten')) {
	/**
	 * shorten method
	 * returns the short string based on $length if string's length is more than $length
	 * 
	 * @param string $str
	 * @param number $length
	 * @param bool $raw
	 * @return string
	 */
	function shorten($str = '', $length = null, $raw = false)
	{
	    if ($length === null) {
	        $length = Configure::read('CakeTools.settings.shorten_length');
	    }

		if (mb_strlen($str) > $length) {
			$shortStr = mb_substr($str, 0, $length)."...";

			if ($raw) {
				return h($shortStr);
			}
		} else {
			return h($str);
		}

		return '<span title="'.h(str_ireplace("/", "", $str)).'">'.h($shortStr).'</span>';
	}
}


if (!function_exists('safeJsonEncode')) {

    /**
     * safeJsonEncode method
     *
     * @param string $value            
     * @return string
     */
    function safeJsonEncode($value)
    {
        return json_encode($value, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS);
    }
}



if (!function_exists('getRealIp')) {
	/**
	 * getRealIp method
	 * 
	 * @return string
	 */
	function getRealIp()
    {
        if (empty($_SERVER['REMOTE_ADDR'])) {
            return '';
        }
        
        if (! empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (! empty($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (! empty($_SERVER['HTTP_VIA'])) {
            $ip = $_SERVER['HTTP_VIA'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        return $ip;
    }
}


if (!function_exists('rm_rf')) {

    /**
     * rm_rf method
     * "rm -rf" command directory
     *
     * @param string $path
     * @throws Exception            
     * @return bool
     */
    function rm_rf($path)
    {
        if (@is_dir($path) && is_writable($path)) {
            $dp = opendir($path);
            while ($ent = readdir($dp)) {
                if ($ent == '.' || $ent == '..') {
                    continue;
                }
                $file = $path . DIRECTORY_SEPARATOR . $ent;
                if (@is_dir($file)) {
                    rm_rf($file);
                } elseif (is_writable($file)) {
                    unlink($file);
                } else {
                    throw new \Exception($file.'. is not writable and cannot be removed. Please fix the permission or select a new path');
                }
            }
            closedir($dp);
            return rmdir($path);
        } else {
            return @unlink($path);
        }
    }
}


if (!function_exists('copyall')) {
    /**
     * copyall method
     * recursively copies files and directories
     * 
     * @param string $src
     * @param string $dst
     * @return bool
     */
    function copyall($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        
        $status = true;
        while (($file = readdir($dir)) !== false) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . DS . $file)) {
                    if (copyall($src . DS . $file, $dst . DS . $file)) {
                        ;
                    } else {
                        $status = false;
                        break;
                    }
                } else {
                    if (copy($src . DS . $file, $dst . DS . $file)) {
                        ;
                    } else {
                        $status = false;
                        break;
                    }
                }
            }
        }
        closedir($dir);

        return $status;
    }
}

if (!function_exists('getQueryParams')) {
	/**
	 * getQueryParams method - parses the url and returns the specified or all list of params
	 *
	 * @access public
	 * @param string $url
	 * @param string $params
	 * @param bool $onlyQuery - if true the param will be checked only in the query string, default - false
	 * @return mixed - string if found the param, bool false otherwise
	 */
	function getQueryParams($url, $param = '', $onlyQuery = false)
	{
		if (!$onlyQuery && in_array($param, ['scheme', 'host', 'path', 'query'])) {
			$p = parse_url($url);
			return isset($p[$param]) ? $p[$param] : false;
		}
	
		$parts = parse_url($url, PHP_URL_QUERY);
		parse_str($parts, $queryParams);
	
		if ($param) {
			return !empty($queryParams[$param]) ? $queryParams[$param] : false;
		}
	
		return $queryParams;
	}
}




if (!function_exists('getClassName')) {
	/**
	 * getClassName - returns class name from object - without namespace
	 *
	 * @param object $object
	 * @return string
	 */
	function getClassName($object = '')
	{
		return getLastValue(explode("\\", get_class($object)));
	}
}


if (!function_exists('extractNumber')) {

    /**
     * extractNumber method - returns numbers from the string
     *
     * @param string $str            
     * @return string
     */
    function extractNumber($str = '')
    {
        return trim(rtrim(trim(preg_replace("/[^0-9\.]/", "", $str)), '.'));
    }
}

if (!function_exists('secondsToHourMinute')) {

    /**
     * secondsToHourMinute method
     * converts given seconds to hours and minutes
     *
     * @param number $seconds            
     * @return string
     */
    function secondsToHourMinute($seconds = null)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        $seconds = $seconds % 60;
        
        return sprintf("%02d:%02d", $hours, $minutes);
    }
}

	

if (!function_exists('secondsToTime')) {
	/**
	 * method secondsToTime - converts seconds to days hours minutes seconds
	 * 							e.g. 33333 => 2 Days 1 Hour 23 Minutes 33 Seconds
	 *
	 * @param int $seconds
	 * @param bool $showSeconds - whether include seconds in result or no
	 * @return string
	 * @link http://stackoverflow.com/a/8273826/932473
	 */
	function secondsToTime($seconds = null, $showSeconds = true)
	{
		if (!$seconds || !is_natural($seconds)) {
			return 0;
		}

		// extract days
		$days = floor($seconds / DAY);

		// extract hours
		$hourSeconds = $seconds % DAY;
		$hours = floor($hourSeconds / HOUR);

		// extract minutes
		$minuteSeconds = $hourSeconds % HOUR;
		$minutes = floor($minuteSeconds / MINUTE);

		// extract the remaining seconds
		$remainingSeconds = $minuteSeconds % MINUTE;
		$seconds = ceil($remainingSeconds);

		// return the final array
		$arr = [
			'd' => (int) $days,
			'h' => (int) $hours,
			'm' => (int) $minutes,
			's' => (int) $seconds,
		];

		$response = '';
		foreach ($arr as $k => $v) {
			if ($k == 'd' && $v) {
				$response = $v . ' ' . ($v == 1 ? __('Day') : __('Days'));
				continue;
			} elseif ($k == 'h' && ($v || $response)) {
				$response .= $response ? ' ' : '';
				$response .= $v . ' ' . ($v == 1 ? __('Hour') : __('Hours'));
			} elseif ($k == 'm' && ($v || $response)) {
				$response .= $response ? ' ' : '';
				$response .= $v . ' ' . ($v == 1 ? __('Minute') : __('Minutes'));
			} elseif ($k == 's' && $showSeconds && ($v || $response)) {
				$response .= $response ? ' ' : '';
				$response .= $v . ' ' . ($v == 1 ? __('Second') : __('Seconds'));
			}
		}

		return $response;
	}
}

if (!function_exists('isCli')) {
    
    /**
     * isCli method
     * check if the current request is from CLI 
     * 
     * @return boolean
     */
    function isCli() {
        return (php_sapi_name() === 'cli');
    }
}






/**********************************************************
 * 
 * 
 *                BAKE FUNCTIONS
 * 
 * 
 **********************************************************/

if (Configure::read('CakeTools.config.bake_functions')) :
    if (!function_exists('bake_get_prefix')) 
    {

        /**
         * bake_get_prefix method
         *
         * @param string $prefix            
         * @return string
         */
        function bake_get_prefix($prefix)
        {
            return str_replace('\\', '', strtolower($prefix));
        }
    }
    
    
    if (!function_exists('bake_get_model_fields')) {
        /**
         * bake_get_model_fields method
         * returns the fields for current action - based on model and type
         *
         * @param string $modelClass
         * @param string $type - can be: model, form, view, index
         * @return array - field list in field(string) => data(array) format
         */
        function bake_get_model_fields($modelClass = null, $type = null)
        {
            if (!in_array($type, ['model', 'form', 'view', 'index'])) {
                echo "Invalid type for bake_get_model_fields";
                die;
            }
    
            $response = [
                '_fields' => [],
                '_tinymceFields' => [],
                '_modelConstStatus' => "Const".Inflector::singularize($modelClass)."Status",
                '_listCheckbox' => '',
                '_actions' => [],
                '_displayField' => ''
            ];
    
            $table = Inflector::underscore($modelClass);
            $fieldsConfig = Configure::read('CakeTools.bake_config.fields');
    
            $dbManager = \Cake\Datasource\ConnectionManager::get('default');
            $dbCollection = $dbManager->schemaCollection();
            $schema = $dbCollection->describe($table);
    
            $fields = [];
            $fieldList = $schema->columns();
            foreach ($fieldList as $field) {
                $fields[$field] = $schema->column($field);
            }
    
            // check if for some model it is set
    
            /**
             * CASE 1
             */
            $includedFields = [];
            $ordered = false;
            if (!empty($fieldsConfig['models'][$modelClass][$type]['include'])) {
                // when include is provided - use that order
                $ordered = true;
    
                $includedFields = $fieldsConfig['models'][$modelClass][$type]['include'];
    
                // add global include fields
                if (!empty($fieldsConfig[$type]['include'])) {
                    $includedFields = array_merge($includedFields, $fieldsConfig[$type]['include']);
                }
    
                // exclude defined fields
                if (!empty($fieldsConfig[$type]['exclude'])) {
                    $includedFields = array_unset($includedFields, $fieldsConfig[$type]['exclude']);
                }
    
            } elseif (!empty($fieldsConfig['models'][$modelClass][$type]['exclude'])) {
                /**
                 * CASE 2
                 */
                $includedFields = $fieldList;
    
                $excludedFields = [];
                if (!empty($fieldsConfig[$type]['exclude'])) {
                    $excludedFields = array_merge($fieldsConfig[$type]['exclude'], $fieldsConfig['models'][$modelClass][$type]['exclude']);
                }
    
                if (!empty($excludedFields)) {
                    $includedFields = array_unset($includedFields, $excludedFields);
                }
            } elseif (!empty($fieldsConfig[$type]['include'])) {
                /**
                 * CASE 3
                 */
                $includedFields = $fieldsConfig[$type]['include'];
    
                // check global exclude
                if (!empty($fieldsConfig[$type]['exclude'])) {
                    $includedFields = array_unset($includedFields, $fieldsConfig[$type]['exclude']);
                }
            } else {
                /**
                 * CASE 4
                 */
                $includedFields = $fieldList;
    
                if (!empty($fieldsConfig[$type]['exclude'])) {
                    $includedFields = array_unset($includedFields, $fieldsConfig[$type]['exclude']);
                }
            }
    
            if (!$ordered) {
                if ($type == 'index') {
                    $fieldGroups = [
                        'id' => [],
                        'string' => [],
                        'select' => [],
                        'numeric' => [],
                        'status' => [],
                        'datetime' => []
                    ];
                } else {
                    $fieldGroups = [
                        'select' => [],
                        'string' => [],
                        'numeric' => [],
                        'datetime' => [],
                        'other' => [],
                        'status' => [],
                        'content' => []
                    ];
                }
    
                foreach($includedFields as $field) {
                    $colType = $schema->columnType($field);
                    $groupType = 'other';
    
                    if ($field == 'id') {
                        $groupType = 'id';
                    } elseif (preg_match('/(_id)$/i', $field)) {
                        $groupType = 'select';
                    } elseif ($field == 'status') {
                        $groupType = 'status';
                    } elseif ($colType == 'string') {
                        $groupType = 'string';
                    } elseif ($colType == 'numeric' || $colType == 'integer') {
                        $groupType = 'numeric';
                    } elseif ($colType == 'date' || $colType == 'datetime') {
                        $groupType = 'datetime';
                    } elseif ($colType == 'text') {
                        $groupType = 'content';
                    } else {
                        $groupType = 'other';
                    }
    
                    if (array_key_exists($groupType, $fieldGroups)) {
                        $fieldGroups[$groupType][] = $field;
                    }
                }
    
                $includedFields = call_user_func_array('array_merge', $fieldGroups);
            }
    
            /**
             * extract only feilds that are present in original $fields array
             * use foreach to keep the order(in case model's include was provided)
             */
            foreach ($includedFields as $field) {
                if (!empty($fields[$field])) {
                    $response['_fields'][$field] = $fields[$field];
                }
            }
    
            $tinymceFields = [];
            if ($type == 'form') {
                // check tinymce fields for form
                if (!empty($fieldsConfig['models'][$modelClass]['form']['tinymce'])) {
                    $tinymceFields = $fieldsConfig['models'][$modelClass][$type]['tinymce'];
                }
    
                if (!empty($fieldsConfig['form']['tinymce'])) {
                    $tinymceFields = array_merge($tinymceFields, $fieldsConfig['form']['tinymce']);
                }
    
                // filter by fields for this model
                if (!empty($tinymceFields)) {
                    $response['_tinymceFields'] = array_intersect($tinymceFields, $fieldList);
                }
            } elseif ($type == 'index') {
                // check listcheckbox option
                $listCheckbox = false;
                if (isset($fieldsConfig['models'][$modelClass]['index']['listCheckbox'])) {
                    $response['_listCheckbox'] = $fieldsConfig['models'][$modelClass]['index']['listCheckbox'];
                } elseif (isset($fieldsConfig['index']['listCheckbox'])) {
                    $response['_listCheckbox'] = $fieldsConfig['index']['listCheckbox'];
                }
    
                $actions = false;
                // check actions
                if (!empty($fieldsConfig['models'][$modelClass]['index']['actions'])) {
                    $response['_actions'] = $fieldsConfig['models'][$modelClass]['index']['actions'];
    
                    // merge with global actions
                    if (!empty($fieldsConfig['index']['actions'])) {
                        $response['_actions'] = array_merge($fieldsConfig['index']['actions'], $response['_actions']);
                    }
                } elseif (isset($fieldsConfig['index']['actions'])) {
                    $response['_actions'] = $fieldsConfig['index']['actions'];
                }
    
                // for index always exclude all text/binary fields
                foreach ($response['_fields'] as &$field) {
                    if ($field['type'] == 'text' || $field['type'] == 'binary') {
                        $field = null;
                    }
                }
                unset($field);
    
                $response['_fields'] = array_filter($response['_fields']);
            } elseif ($type == 'model') {
                // check displayField
                if (!empty($fieldsConfig['models'][$modelClass]['model']['displayField'])) {
                    $response['_displayField'] = $fieldsConfig['models'][$modelClass]['model']['displayField'];
                } elseif (!empty($fieldsConfig['model']['displayField'])) {
                    $response['_displayField'] = $fieldsConfig['model']['displayField'];
                }
            }
    
            return $response;
        }
    }
    
    
    if (!function_exists('bake_get_belongs_to')) {
        /**
         * bake_get_belongs_to method
         *
         * @param object $model
         * @return array - in foreign_key => Model format
         */
        function bake_get_belongs_to($model = null)
        {
            $extractor = function ($val) {
                return [
                    $val->foreignKey() => $val->target()
                ];
            };
    
            $belongsTo = $model->associations()->type('BelongsTo');
            if (empty($belongsTo)) {
                return [];
            }
    
            $data = array_map($extractor, $model->associations()->type('BelongsTo'));
            return call_user_func_array('array_merge', $data);
        }
    }

endif;
