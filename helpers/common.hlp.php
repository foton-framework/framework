<?php



class h_common
{
	
	//--------------------------------------------------------------------------
	
	static function redirect($url, $_301 = FALSE)
	{
		ob_get_level() && ob_clean();
		if ($_301) header("HTTP/1.1 301 Moved Permanently");
		header("Location: {$url}");
		exit;
	}
	
	//--------------------------------------------------------------------------
	
	static function redirect_back()
	{
		$back_link    = preg_replace('/^(https?:\/\/'.preg_quote($_SERVER['HTTP_HOST'], '/').')?\/*(.*?)\/*$/i', '$2', self::back_link());
		$current_page = preg_replace('/^\/*(.*?)\/*$/i', '$1', $_SERVER[sys::$config->uri->source]);
		if ($back_link == $current_page) $back_link = ! empty(sys::$config->sys->base_url) ? sys::$config->sys->base_url : '/';
		elseif ( ! $back_link) $back_link = '/';
		elseif($back_link != '/') $back_link = '/' . $back_link . '/';
		self::redirect($back_link);
	}
	
	//--------------------------------------------------------------------------
	
	static function back_link()
	{
		return htmlspecialchars(empty($_POST['back_link']) ? (empty($_SERVER['HTTP_REFERER']) ? '/' : $_SERVER['HTTP_REFERER']) : $_POST['back_link']);
	}
	
	//--------------------------------------------------------------------------
	
	static function cut_text($text, $length = 300)
	{
		$text = strip_tags($text, '');
		if (mb_strlen($text) > $length)
		{
			if (($cut = strpos($text, '[CUT]')) !== FALSE)
			{
				return substr($text, 0, $cut) . '...';
			}
			$text = substr($text, 0, strpos($text, ' ', $length));
			$text = preg_replace('/[,.:?!-]+$/i', '', $text);
			return $text . '...';
		}
		else
		{
			return $text;
		}
	}
	
	//--------------------------------------------------------------------------
	
	static function date($time, $format = 'd ?, Y, H:i')
	{
		$d = date('YmdHis', time()) - date('YmdHis', $time);
		if ($d < 60*60*24) return 'Сегодня, ' . date('H:i', $time);
		elseif ($d < 60*60*24) return 'Вчера, ' . date('H:i', $time);
		$month = array('Января', 'Февраля', 'Марта', 'Апреля', 'Мая', 'Июня', 'Июля', 'Августа', 'Сентября', 'Октября', 'Ноября', 'Декабря');
		return str_replace('?', $month[date('m', $time)-1], date($format, $time));
	}
	
	//--------------------------------------------------------------------------
	
	static function nicetime($timestamp, $detailLevel = 1)
	{
		$periods = array("секунд", "минут", "часов", "дней", "недель", "месяцев", "лет", "десятилетий");
		$lengths = array("60", "60", "24", "7", "4.35", "12", "10");
		$now = time();
		
		// check validity of date
		if(empty($timestamp)) return;
		
		
		
		// is it future date or past date
		if($now > $timestamp)
		{
			$difference = $now - $timestamp;
			$tense = "назад";
		}
		else
		{
			$difference = $timestamp - $now;
			$tense = "from now";
		}
		
		if ($difference == 0)
		{
			return "1 секунда назад";
		}
		
		$remainders = array();
		for($j = 0; $j < count($lengths); $j++)
		{
			$remainders[$j] = floor(fmod($difference, $lengths[$j]));
			$difference = floor($difference / $lengths[$j]);
		}
		
		$difference = round($difference);
		$remainders[] = $difference;
		$string = "";
		for ($i = count($remainders) - 1; $i >= 0; $i--)
		{
			if ($remainders[$i])
			{
				$string .= $remainders[$i] . " " . $periods[$i];
				$string .= " ";
				$detailLevel--;
				if ($detailLevel <= 0) break;
			}
		}
		
		return $string . $tense;
	}
	
	
	//--------------------------------------------------------------------------
	
	static function translit($str) 
	{
		static $valid_chars;
		
		$tr = array(
			"А"=>"a","Б"=>"b","В"=>"v","Г"=>"g",
			"Д"=>"d","Е"=>"e","Ж"=>"j","З"=>"z","И"=>"i",
			"Й"=>"y","К"=>"k","Л"=>"l","М"=>"m","Н"=>"n",
			"О"=>"o","П"=>"p","Р"=>"r","С"=>"s","Т"=>"t",
			"У"=>"u","Ф"=>"f","Х"=>"h","Ц"=>"c","Ч"=>"ch",
			"Ш"=>"sh","Щ"=>"sch","Ъ"=>"","Ы"=>"y","Ь"=>"",
			"Э"=>"e","Ю"=>"yu","Я"=>"ya","а"=>"a","б"=>"b",
			"в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
			"з"=>"z","и"=>"i","й"=>"y","к"=>"k","л"=>"l",
			"м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
			"с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
			"ц"=>"c","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
			"ы"=>"y","ь"=>"","э"=>"e","ю"=>"yu","я"=>"ya","'"=>"","ё"=>"yo","Ё"=>"yo"
		);
		
		if ( ! $valid_chars)
		{
			$valid_chars = preg_quote(implode('', $tr), '/');
		}
		
		$str = strtr($str, $tr);
		$str = preg_replace("/[^a-z0-9()]+/i", '-', $str);
		$str = preg_replace('/^-?(.*?)-?$/i', '\1', $str);
		
		return strtolower($str);
	}

	//--------------------------------------------------------------------------
}