<?php defined('EXT') OR die('No direct script access allowed');



class SYS_Image
{
	public $file_name = '';
	
	//--------------------------------------------------------------------------
	
	public function set_file_name($name)
	{
		$this->file_name = $name;
	}
	
	//--------------------------------------------------------------------------
	
	public function file_name()
	{
		return $this->file_name;
	}
	
	//--------------------------------------------------------------------------
	
	public function process($filename, $config, $prepare = FALSE)
	{	
		if ($prepare) $this->_prepare($filename, $config);
		
		foreach ($config as $opt)
		{
			$dist = ROOT_PATH . $opt['dist'] . $this->file_name();
			$this->thumb($filename, $dist, $opt['size'][0], $opt['size'][1], empty($opt['crop']) ? FALSE : TRUE, empty($opt['quality']) ? 95 : $opt['quality']);
			
			$result[] = $dist;
		}
		
		return isset($result) ? $result : FALSE;
	}
	
	//--------------------------------------------------------------------------
	
	function _prepare($filename, $config, $quality = 95)
	{	
		$outer = TRUE;
		$k   = 2;
		$max = array(0, 0);
		foreach ($config as $avatar)
		{
			$max = array( max($max[0], $avatar['size'][0]), max($max[1], $avatar['size'][1]));
		}
		
		$new   = array($max[0] * $k, $max[1] * $k);
		$src   = getimagesize($filename);
		
		// Ratio
		$r = array($new[0]/$new[1], $src[0]/$src[1]);
		
		// Type
		$t = ($r[0] >= $r[1]) ^ $outer;
		
		// New size
		$new[0] = $t^0 ? $new[$t] * $r[1] : $new[$t];
		$new[1] = $t^1 ? $new[$t] / $r[1] : $new[$t];


		$isrc = imagecreatefromjpeg($filename);
		$inew = imagecreatetruecolor($new[0], $new[1]);
		
		imagecopyresized($inew, $isrc, 0, 0, 0, 0, $new[0], $new[1], $src[0], $src[1]);
		
//		$this->blur($inew);
		
/*
		if ( ! ob_get_contents())
		{
			header('Content-type: image/jpeg');
			imagejpeg($inew);
		}
*/
		
		imagejpeg($inew, $filename, $quality);
	}
	
	//------------------------------------------------------------------------------
	
	public function thumb($filename, $destination, $th_width, $th_height, $forcefill = FALSE, $quality = 95)
	{
		list($width, $height) = getimagesize($filename);
		$source = imagecreatefromjpeg($filename);
		
		if( ! ($width > $th_width || $height > $th_height))
		{
			copy($filename, $destination);
			return;
		}
		
		$a = $th_width / $th_height;
		$b = $width / $height;

		if(($a > $b) ^ $forcefill)
		{
			$src_rect_width  = $a * $height;
			$src_rect_height = $height;

			if( ! $forcefill)
			{
				$src_rect_width = $width;
				$th_width = $th_height / $height * $width;
			}
		}
		else
		{
			$src_rect_height = $width / $a;
			$src_rect_width  = $width;

			if( ! $forcefill)
			{
				$src_rect_height = $height;
				$th_height = $th_width / $width * $height;
			}
		}
		
		$src_rect_xoffset = ($width - $src_rect_width)   / 2 * intval($forcefill);
		$src_rect_yoffset = ($height - $src_rect_height) / 2 * intval($forcefill);

		$k = 2;
		$blur = imagecreatetruecolor($th_width * $k, $th_height * $k);
		imagecopyresized($blur, $source, 0, 0, $src_rect_xoffset, $src_rect_yoffset, $th_width * $k, $th_height * $k, $src_rect_width, $src_rect_height);
		$this->blur($blur);
		
		$thumb = imagecreatetruecolor($th_width, $th_height);		
		imagecopyresized($thumb, $blur, 0, 0, 0, 0, $th_width, $th_height, $th_width * $k, $th_height * $k);
		
		imagejpeg($thumb, $destination, $quality);
	}

	//--------------------------------------------------------------------------
	
	function blur($im)
	{
		if(function_exists('imageconvolution'))
		{
			$gaussian = array(array(1.0, 2.0, 1.0), array(2.0, 4.0, 2.0), array(1.0, 2.0, 1.0));
			imageconvolution($im, $gaussian, 16, 0);
		}
		else
		{
			// w00t. my very own blur function!
			$width  = imagesx($im);
			$height = imagesy($im);
	 
			// the higher, the more blurred (no impact on speed)
			// however, values>2 don't look very good. Sorry.
			$distance = 1;
	 
			$temp_im = ImageCreateTrueColor($width,$height);
			ImageCopy($temp_im,$im,0,0,0,0,$width,$height);
			/*
			we *could* use this: 
			ImageCopy($temp_im,$im,0,0,0,$distance,$width,$height);
			instead, but that leads to an anomally at the top of the image.		
			*/
	 
			// blur by merging with itself at different x/y offsets:
			$pct = 70; // based on empirical tests, 70% gives the most blur.
			ImageCopyMerge($temp_im, $im, 0, 0, 0, $distance, $width-$distance, $height-$distance, $pct);
			ImageCopyMerge($im, $temp_im, 0, 0, $distance, 0, $width-$distance, $height, $pct);
			ImageCopyMerge($temp_im, $im, 0, $distance, 0, 0, $width, $height, $pct);
			ImageCopyMerge($im, $temp_im, $distance, 0, 0, 0, $width, $height, $pct);
	 
			// remove temp image
			ImageDestroy($temp_im);
		}
	}
}