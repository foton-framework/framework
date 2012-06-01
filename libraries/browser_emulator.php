<?php

// $emulator = new Browser_Emulator();
// $emulator->base_url('');
// $emulator->replace_js(TRUE);
// $emulator->replace_link(TRUE);
// $emulator->replace_form(TRUE);
// $emulator->get('https://www.google.com/search?q=test&hl=en&gbv=1&tbm=isch&ei=OJ7IT5OSBLPT4QSN_fjsDw&start=20&sa=N');
// // OR:
// // $emulator->process();
// echo $emulator->parsed_content();

class SYS_Browser_Emulator
{
	//--------------------------------------------------------------------------

	private $base_url    = '';
	private $cookie_path = 'temp/';
	private $cmd_key = array(
		'url'     => 'u',
		'referer' => 'r',
		'method'  => 'm',
	);

	private $replace_js    = FALSE;
	private $replace_link  = FALSE;
	private $replace_form  = FALSE;
	// private $replace_img   = FALSE;
	private $method        = 'post';
	private $request       = NULL;
	private $session_id    = NULL;
	private $content       = NULL;
	private $referer       = NULL;
	private $headers       = array();
	private $log           = array();
	private $request_data  = array();

	//--------------------------------------------------------------------------

	public function __construct()
	{
		$this->request = new stdClass;
	}

	//--------------------------------------------------------------------------

	public function session_id($val = NULL)
	{
		if ($val !== NULL) $this->session_id = $val;
		return $this->session_id;
	}

	//--------------------------------------------------------------------------

	public function content($val = NULL)
	{
		if ($val !== NULL) $this->content = $val;
		return $this->content;
	}

	//--------------------------------------------------------------------------

	public function method($val = NULL)
	{
		if ($val !== NULL) $this->method = $val;
		return $this->method;
	}

	//--------------------------------------------------------------------------

	public function cookie_path($val = NULL)
	{
		if ($val !== NULL) $this->cookie_path = $val;
		return $this->cookie_path;
	}

	//--------------------------------------------------------------------------

	public function cookie_file()
	{
		return $this->cookie_path() . $this->request->domain . '.txt';
	}

	//--------------------------------------------------------------------------

	public function parsed_content()
	{
		$content = $this->content();

		// foreach ($this->headers as $key => $val)
		// {
		// 	if ($key) header($key . ': ' . $val);
		// }
		if ($this->headers['content-type'])
		{
			header('Content-type: ' . $this->headers['content-type']);
		}

		if (strpos($this->headers['content-type'], 'text/html') !== FALSE)
		{
			$site_url = $this->request->scheme . '://' . $this->request->host;
			
			$content = preg_replace('/((href|src)=["\']?)\/([^\/])/ui', '$1' . $site_url . '/$3' , $content);

			if ($this->replace_form())
			{
				$content = preg_replace_callback('/(<form [^>]*>)/ui', array($this, '_replace_form_callback'), $content);
			}
			if ($this->replace_js())
			{
				$content = preg_replace('@<script[^>]*?.*?</script>@siu', '', $content);
			}
			if ($this->replace_link())
			{
				$content = preg_replace_callback('/(<a [^>]*href=(["\']))([^\2]+?)(\2[^<]*>)/ui', array($this, '_replace_link_callback'), $content);
			}
		}

		return $content;
	}

	//--------------------------------------------------------------------------

	public function _replace_form_callback($form)
	{
		$attr = array();
		preg_match_all('/([\w]+)[ ]*=(["\' ]?)([^"\']*)[\2]?/i', $form[0], $matches);

		if (count($matches))
		{
			foreach ($matches[1] as $i => $key) $attr[$key] = $matches[3][$i];
		}
		
		array_walk($attr, 'trim');

		if ($attr['action'])
		{
			if ($attr['action']{0} == '/') $attr['action'] = $this->request->url . (substr($this->request->url, -1) == '/'?'':'/') . substr($attr['action'], 1);
			// elseif (substr($attr['action'], 0, 4) != 'http') $attr['action'] = $this->request->url . $attr['action'];
		}
		$attr['action'] = $this->base_url() 
			. '?' . $this->cmd_key['url'] . '=' . urlencode(str_replace('&amp;', '&', $attr['action'] ? $attr['action'] : $this->request->url))
			. '&' . $this->cmd_key['referer'] . '=' . urlencode($this->request->url);
		if (empty($attr['method']) || strtolower($attr['method'])=='get')
		{
			$attr['action'] .= '&' . $this->cmd_key['method'] . '=get';
		}
		$attr['method'] = 'post';

		$result = '';
		foreach ($attr as $key => $val) $result .= " {$key}=\"{$val}\"";

		return "<form{$result}>";
	}

	// //--------------------------------------------------------------------------

	public function _replace_link_callback($matches)
	{
		if (substr($matches[3], 0, 4) != 'http') return $matches[0];
		return $matches[1] . $this->base_url() . '?' . $this->cmd_key['url'] . '=' . urlencode(str_replace('&amp;', '&', $matches[3])) . $matches[4];
		// echo htmlspecialchars(print_r($matches, 1));
	}

	//--------------------------------------------------------------------------

	public function base_url($val = NULL)
	{
		if ($val !== NULL) $this->fake_url = $val;
		return $this->fake_url;
	}

	//--------------------------------------------------------------------------

	public function replace_link($val = NULL)
	{
		if ($val !== NULL) $this->replace_link = (bool)$val;
		return $this->replace_link;
	}

	//--------------------------------------------------------------------------

	public function replace_js($val = NULL)
	{
		if ($val !== NULL) $this->replace_js = (bool)$val;
		return $this->replace_js;
	}

	//--------------------------------------------------------------------------

	public function replace_form($val = NULL)
	{
		if ($val !== NULL) $this->replace_form = (bool)$val;
		return $this->replace_form;
	}

	//--------------------------------------------------------------------------

	// public function replace_img($val = NULL)
	// {
	// 	if ($val !== NULL) $this->replace_img = (bool)$val;
	// 	return $this->replace_img;
	// }
	
	//--------------------------------------------------------------------------

	public function log($key = NULL, $msg = NULL)
	{
		if ($key && $msg) $this->log[] = $key . ': ' . $msg;
		return $this->log;
	}

	//--------------------------------------------------------------------------

	public function referer($val = NULL)
	{
		if ($val !== NULL) $this->referer = $val;
		return $this->referer;
	}

	//--------------------------------------------------------------------------

	public function user_agent()
	{
		return $_SERVER['HTTP_USER_AGENT'];
	}

	//--------------------------------------------------------------------------

	public function request_data($key = NULL, $val = NULL)
	{
		if ($key === FALSE)
		{
			$this->request_data = array();
		}

		if ($key)
		{
			if ($val)
			{
				$this->request_data[$key] = $val;
			}
			else
			{
				if (is_array($key))
				{
					$this->request_data = array_merge($this->request_data, $key);
				}
				else
				{
					$list = explode('&', $key);
					foreach ($list as $row)
					{
						$row = explode('=', $row);
						if (count($row) == 2) $this->request_data[$row[0]] = $row[1];
					}
				}
			}
		}
		
		return $this->request_data;
	}

	//--------------------------------------------------------------------------

	public function request_data_str()
	{
		$resutl = '';
		foreach ($this->request_data as $key => $val)
		{
			$resutl .= ($resutl ? '&' : '') . $key . '=' . $val;
		}

		return $resutl;
	}

	//--------------------------------------------------------------------------

	public function get($request)
	{
		$this->log('GET', $request);

		if (substr($request, 0, 4) != 'http') $request = 'http://' . $request;

		$param = parse_url($request);

		foreach ($param as $key => $val) $this->request->$key = $val;
		$this->request->src    = $request;
		$this->request->domain = preg_replace('/^.*?\.?([^\.]+\.[^\.]{2,5})$/i', '\1', $this->request->host);
		
		if ($this->request_data() && $this->method() == 'get')
		{
			$this->request->query = $this->request_data_str();
		}
		
		$this->request->url    = $this->request->scheme . '://' 
			. $this->request->host 
			. (empty($this->request->path) ? '' : $this->request->path) 
			. (empty($this->request->query) ? '' : '?' . $this->request->query);

		$this->referer($this->request->url);
		
		// инициализация cURL
		$ch = curl_init($this->request->url);

		// получать заголовки
		curl_setopt($ch, CURLOPT_HEADER, 1);

		// Устанавливаем USER_AGENT
		curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent());

		// елси проверятся откуда пришел пользователь, то указываем допустимый заголовок HTTP Referer:
		if ($this->referer())
		{
			curl_setopt ($ch, CURLOPT_REFERER, $this->referer());
		}

		if ($this->request_data() && $this->method() == 'post')
		{
			// использовать метод POST
			curl_setopt ($ch, CURLOPT_POST, 1);
			
			// передаем поля формы
			curl_setopt ($ch, CURLOPT_POSTFIELDS, $this->request_data_str());
		}

		// сохранять информацию Cookie в файл, чтобы потом можно было ее использовать
		curl_setopt ($ch, CURLOPT_COOKIEJAR, $this->cookie_file());

		curl_setopt ($ch, CURLOPT_COOKIEFILE, $this->cookie_file());
		
		// возвращать результат работы
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);

		// не проверять SSL сертификат
		curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);

		// не проверять Host SSL сертификата
		curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);

		// это необходимо, чтобы cURL не высылал заголовок на ожидание
		curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Expect:'));

		// выполнить запрос
		curl_exec ($ch);

		// получить результат работы
		$this->_curl_get_content($ch);

		// закрыть сессию работы с cURL
		curl_close($ch);
	}

	//--------------------------------------------------------------------------

	private function _curl_get_content($ch)
	{
		$result = curl_multi_getcontent($ch);

		$nl = strpos(substr($result, 0, 300), "\r\n") ? "\r\n" : "\n";
		
		$headers_length = strpos($result, $nl . $nl);

		$headers_text = explode("\n", trim(substr($result, 0, $headers_length)));
		$content = substr($result, $headers_length + strlen($nl) * 2);

		$this->headers = array();
		foreach ($headers_text as $i => $row)
		{
			if ( ($divider_pos = strpos($row, ':')) )
			{
				$key = trim(substr($row, 0, $divider_pos));
				$val = trim(substr($row, $divider_pos+1));
				$this->headers[strtolower($key)] = $val;
			}
			else
			{
				$this->headers[] = $val;
			}
		}
		// $this->headers($headers);

		if (isset($this->headers['location']))
		{
			$this->log('REDIRECT', $this->headers['location']);
			return $this->get($this->headers['location']);
		}

		$this->content($content);
	}

	//--------------------------------------------------------------------------

	public function process()
	{
		if (empty($_GET[$this->cmd_key['url']])) return;

		if ($_GET[$this->cmd_key['referer']])
		{
			$this->referer($_GET[$this->cmd_key['referer']]);
		}

		if (count($_POST))
		{
			if (isset($_GET[$this->cmd_key['method']]) && $_GET[$this->cmd_key['method']] == 'get')
			{
				$this->method('get');
				$this->request_data($_POST);
			}
			else
			{
				$this->method('post');
				$this->request_data($_POST);
			}
			
		}

		$this->get($_GET[$this->cmd_key['url']]);
	}

	//--------------------------------------------------------------------------

}