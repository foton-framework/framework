<?php


class h_url
{

	public static function redirect($url = NULL)
	{
		ob_get_level() && ob_clean();
		
		$url = self::url($url);
		
		header("Location: {$url}");
	}
	
	//--------------------------------------------------------------------------
	
	public static function link($url, $content, $extra = '')
	{
		$url = self::url($url);
		if ($extra) $extra = ' ' . $extra;
		return "<a href=\"{$url}\"{$extra}>{$content}</a>";
	}
	
	//--------------------------------------------------------------------------
	
	public static function url($url)
	{
		if ( ! $url)
		{
			$url = sys::$config->sys->base_url;
		}
		elseif (substr($url, 0, 7) != 'http://' && $url{0} != '/')
		{
			$url = sys::$config->sys->base_url . $url;
		}
		
		if (preg_match('/[^\/]$/i', $url)) $url .= '/';
		
		return $url;
	}
}