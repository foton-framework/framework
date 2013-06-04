<?php defined('EXT') OR die('No direct script access allowed');


class SYS_Upload
{
	public $upload_path  = '';
	public $file_name     = '';
	public $allowed_types = '';  // Divider: |
	public $max_size      = 1;

	public $errors     = array();
	
	private $file_data = array();
	private $mimes = array(
		'hqx'	=>	'application/mac-binhex40',
		'cpt'	=>	'application/mac-compactpro',
		'csv'	=>	array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel'),
		'bin'	=>	'application/macbinary',
		'dms'	=>	'application/octet-stream',
		'lha'	=>	'application/octet-stream',
		'lzh'	=>	'application/octet-stream',
		'exe'	=>	'application/octet-stream',
		'class'	=>	'application/octet-stream',
		'psd'	=>	'application/x-photoshop',
		'so'	=>	'application/octet-stream',
		'sea'	=>	'application/octet-stream',
		'dll'	=>	'application/octet-stream',
		'oda'	=>	'application/oda',
		'pdf'	=>	array('application/pdf', 'application/x-download'),
		'ai'	=>	'application/postscript',
		'eps'	=>	'application/postscript',
		'ps'	=>	'application/postscript',
		'smi'	=>	'application/smil',
		'smil'	=>	'application/smil',
		'mif'	=>	'application/vnd.mif',
		'xls'	=>	array('application/excel', 'application/vnd.ms-excel', 'application/msexcel'),
		'ppt'	=>	array('application/powerpoint', 'application/vnd.ms-powerpoint'),
		'wbxml'	=>	'application/wbxml',
		'wmlc'	=>	'application/wmlc',
		'dcr'	=>	'application/x-director',
		'dir'	=>	'application/x-director',
		'dxr'	=>	'application/x-director',
		'dvi'	=>	'application/x-dvi',
		'gtar'	=>	'application/x-gtar',
		'gz'	=>	'application/x-gzip',
		'php'	=>	'application/x-httpd-php',
		'php4'	=>	'application/x-httpd-php',
		'php3'	=>	'application/x-httpd-php',
		'phtml'	=>	'application/x-httpd-php',
		'phps'	=>	'application/x-httpd-php-source',
		'js'	=>	'application/x-javascript',
		'swf'	=>	'application/x-shockwave-flash',
		'sit'	=>	'application/x-stuffit',
		'tar'	=>	'application/x-tar',
		'tgz'	=>	'application/x-tar',
		'xhtml'	=>	'application/xhtml+xml',
		'xht'	=>	'application/xhtml+xml',
		'zip'	=>  array('application/x-zip', 'application/zip', 'application/x-zip-compressed'),
		'mid'	=>	'audio/midi',
		'midi'	=>	'audio/midi',
		'mpga'	=>	'audio/mpeg',
		'mp2'	=>	'audio/mpeg',
		'mp3'	=>	array('audio/mpeg', 'audio/mpg'),
		'aif'	=>	'audio/x-aiff',
		'aiff'	=>	'audio/x-aiff',
		'aifc'	=>	'audio/x-aiff',
		'ram'	=>	'audio/x-pn-realaudio',
		'rm'	=>	'audio/x-pn-realaudio',
		'rpm'	=>	'audio/x-pn-realaudio-plugin',
		'ra'	=>	'audio/x-realaudio',
		'rv'	=>	'video/vnd.rn-realvideo',
		'wav'	=>	'audio/x-wav',
		'bmp'	=>	'image/bmp',
		'gif'	=>	'image/gif',
		'jpeg'	=>	array('image/jpeg', 'image/pjpeg'),
		'jpg'	=>	array('image/jpeg', 'image/pjpeg'),
		'jpe'	=>	array('image/jpeg', 'image/pjpeg'),
		'png'	=>	array('image/png',  'image/x-png'),
		'tiff'	=>	'image/tiff',
		'tif'	=>	'image/tiff',
		'css'	=>	'text/css',
		'html'	=>	'text/html',
		'htm'	=>	'text/html',
		'shtml'	=>	'text/html',
		'txt'	=>	'text/plain',
		'text'	=>	'text/plain',
		'log'	=>	array('text/plain', 'text/x-log'),
		'rtx'	=>	'text/richtext',
		'rtf'	=>	'text/rtf',
		'xml'	=>	'text/xml',
		'xsl'	=>	'text/xml',
		'mpeg'	=>	'video/mpeg',
		'mpg'	=>	'video/mpeg',
		'mpe'	=>	'video/mpeg',
		'qt'	=>	'video/quicktime',
		'mov'	=>	'video/quicktime',
		'avi'	=>	'video/x-msvideo',
		'movie'	=>	'video/x-sgi-movie',
		'doc'	=>	'application/msword',
		'docx'	=>	'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'xlsx'	=>	'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		'word'	=>	array('application/msword', 'application/octet-stream'),
		'xl'	=>	'application/excel',
		'eml'	=>	'message/rfc822'
	);
	
	//--------------------------------------------------------------------------
	
	public function __construct()
	{
		sys::set_config_items($this, 'upload');
	}
	
	//--------------------------------------------------------------------------
	
	public function init($opt)
	{
		foreach ($opt as $key => $val)
		{
			$this->$key = $val;
		}
	}
	
	//--------------------------------------------------------------------------
	
	public function set_field($field)
	{
		$this->field = $field;
	}
	//--------------------------------------------------------------------------
	
	public function field()
	{
		return $this->field;
	}
	
	//--------------------------------------------------------------------------
	
	public function set_allowed_types($allowed_types)
	{
		$this->allowed_types = $allowed_types;
	}
	
	//--------------------------------------------------------------------------
	
	public function set_max_size($max_size)
	{
		$this->max_size = $max_size;
	}
	
	//--------------------------------------------------------------------------
	
	public function set_upload_path($upload_path)
	{
		$this->upload_path = $upload_path;
	}
	
	//--------------------------------------------------------------------------
	
	public function set_file_name($file_name)
	{
		$this->file_name = $file_name;
	}
	
	//--------------------------------------------------------------------------
	
	public function error($name, $prefix = '', $suffix = '', $divider = '<br>')
	{
		if (isset($this->errors[$name]))
		{
			return $prefix . implode($divider, $this->errors[$name]) . $suffix;
		}
	}

	//--------------------------------------------------------------------------

	public function reset()
	{
//		$this->upload_path   = '';
//		$this->allowed_types = '';
//		$this->max_size      = 1;

		$this->file_data = array();
		//$this->errors    = array();
	}
	
	//--------------------------------------------------------------------------
	
	public function run($field = NULL)
	{
		if ($field)
		{
			$this->set_field($field);
		}
	
		if ( ! isset($_FILES[$this->field]) || $_FILES[$this->field]['error'])
		{
			return FALSE;
		}

		$this->file_data = $_FILES[$this->field];

		$this->check_size($this->field);
		$this->check_mime($this->field);
	
		if ( ! isset($this->errors[$this->field]) || ! count($this->errors[$this->field]))
		{
			$ext = '.' . strtolower(pathinfo($this->file_data['name'], PATHINFO_EXTENSION));
			
			if ($this->upload_path)
			{
				
				if ( ! $this->file_name) $this->file_name = pathinfo($this->file_data['name'], PATHINFO_FILENAME);
				
				$fname = $this->file_name;
				$i     = 1;
				while (file_exists($dest = ROOT_PATH . $this->upload_path . '/' . $fname . $ext))
				{
					$fname = $this->file_name . '_' . ($i++);
				}
				$src  = $this->upload_path . $this->file_name;

				move_uploaded_file($this->file_data['tmp_name'], $dest);
				chmod($dest, 0777);
				
				$result = array(
					'file_name' => $this->file_name . $ext,
					'file_path' => $this->upload_path . '/' . $fname . $ext,
					'full_path' => $dest,
					'raw_name'  => $fname,
				);
			}
			else
			{
				$result = array(
					'file_name' => pathinfo($this->file_data['tmp_name'], PATHINFO_FILENAME),
					'file_path' => dirname($this->file_data['tmp_name']),
					'full_path' => $this->file_data['tmp_name'],
					'raw_name'  => '',
				);
			}
			
			$result['file_type'] = $this->file_data['type'];
			$result['file_ext']  = $ext;
			$result['orig_name'] = $this->file_data['name'];
			$result['file_size'] = sprintf('%.2f', $this->file_data['size'] / 1024);
			
			$this->reset();
			
			return (object)$result;	
		}
		
		$this->reset();
		
		return FALSE;
	}

	//--------------------------------------------------------------------------

	public function check_size($name)
	{
		if ( ! $this->max_size)
		{
			return TRUE;
		}

		if ($this->file_data['size'] / 1024 / 1024 > $this->max_size)
		{
			$this->errors[$name][] = 'Файл должен быть меньше ' . number_format($this->max_size, 1) . ' МБ';
			return FALSE;
		}

		return TRUE;
	}

	//--------------------------------------------------------------------------

	//--------------------------------------------------------------------------

	public function check_mime($name)
	{
		if ( ! $this->allowed_types)
		{
			$this->errors[$name][] = 'Не задан тип файла';
			return FALSE;
		}

		$allowed_types = explode('|', $this->allowed_types);


		$mimes = array();
		foreach ($allowed_types as $a_type)
		{
			$a_mime = isset($this->mimes[$a_type]) ? $this->mimes[$a_type] : array();
			is_array($a_mime) ? ($mimes = array_merge($mimes, $a_mime)) : ($mimes[]=$a_mime);
		}
		
		// Check images:
		if (current(explode('/', $this->file_data['type'])) == 'image')
		{
			$info = @getimagesize($this->file_data['tmp_name']);
			$this->file_data['type'] = $info['mime'];
		}

		if ( ! in_array($this->file_data['type'], $mimes))
		{
			$this->errors[$name][] = 'Недопустимый тип файла. Разрешены только файлы: ' . implode(', ', $allowed_types);
			return FALSE;
		}

		return TRUE;
	}

	//--------------------------------------------------------------------------
}
