<?php


class SYS_Pagination
{
	//--------------------------------------------------------------------------
	
	private $_conf;
	private $_group = 'default';
	
	//--------------------------------------------------------------------------
	
	public function init($total, $items = 10, $url = NULL, $link = 'page_?.html')
	{
		if (is_array($total))
		{
			$conf =& $total;
			if (isset($conf['group'])) $this->set_group($conf['group']);
			foreach ($conf as $key => $opt) $this->set_opt($key, $opt);
		}
		else
		{
			if ( ! $url)
			{
				$url = sys::$lib->uri->uri_string();
				$url = preg_replace('/'. str_replace('\\?', '\d+', preg_quote($link,'/')).'$/i', '', $url);
				if ($url{strlen($url)-1} != '/') $url .= '/';
			}
			
			$this->set_opt('total', $total);
			$this->set_opt('url'  , $url);
			if ($items) $this->set_opt('items', $items);
			if ($link)  $this->set_opt('link' , $link);
		}
	}
	
	//--------------------------------------------------------------------------
	
	public function render($group = NULL)
	{
		if ($group !== NULL) $this->set_group($group);
		
		$cur_page = $this->current_page();
		$total    = $this->opt('total');
		$items    = $this->opt('items');
		if ( ! $items) $items = 10;
		$pages    = ceil($total / $items);
		
		if ($cur_page > $pages) hlp::redirect('/' . $this->opt('url'));
		
		$tpl      = $this->default_template();
		$link_tpl = str_replace('?', '%d', '/' . $this->opt('url') . $this->opt('link'));
		
		if ($pages < 2) return;
		
		$max_pages = 12;
		$r_limit = $cur_page + $max_pages/2;
		$l_limit = $cur_page - $max_pages/2;
		
		$result = $tpl['prefix'];
		$_sd_a = $tpl['space_divider'];
		$_sd_b = $tpl['space_divider'];
		for ($p=1; $p<=$pages; $p++)
		{
			if (($p >= $r_limit || $p <= $l_limit) && ($p != $pages && $p != 1))
			{
				if ($p >= $r_limit)
				{
					$result .= $_sd_a;
					$_sd_a = '';
				}
				elseif ($p <= $l_limit)
				{
					$result .= $_sd_b;
					$_sd_b = '';
				}
			}
			else
			{
				$link = $p == 1 ? '/' . $this->opt('url') : sprintf($link_tpl, $p);
				if ($cur_page == $p) $result .= sprintf($tpl['current_page'], $p);
				else $result .= sprintf($tpl['page_link'], $link, $p);
			}
		}
		$result .= $tpl['suffix'];
		return $result;
	}
	
	//--------------------------------------------------------------------------
	
	public function current_page()
	{
		static $cur_page;
		
		$uri_string = sys::$lib->uri->uri_string();
		$uri_mask   = '/' . str_replace('\?', '(\d+)', preg_quote($this->opt('url') . $this->opt('link'), '/')) . '/i';
		
		preg_match($uri_mask, $uri_string, $matches);
		
		return isset($matches[1]) && $matches[1]>0 ? $matches[1] : 1;
	}
	
	//--------------------------------------------------------------------------
	
	public function set_db_limit()
	{
		$cur_page = $this->current_page();
		$items    = $this->opt('items');
		sys::$lib->db->limit(($cur_page-1) * $items, $items);
	}
	
	//--------------------------------------------------------------------------
	
	public function set_total($total)
	{
		$this->set_opt('total', $total);
	}
	
	//--------------------------------------------------------------------------
	
	public function default_template()
	{
		return array(
			'prefix'        => '<div class="pagination">',
			'suffix'        => '</div>',
			'next_link'     => '<a href="%s">Далее</a>',
			'next_link_off' => '',
			'back_link'     => '<a href="%s">Назад</a>',
			'back_link_off' => '',
			'page_link'     => '<a href="%s">%d</a>',
			'current_page'  => '<b>%d</b>',
			'space_divider' => '<span>...</span>'
		);
	}
	
	//--------------------------------------------------------------------------
	
	public function set_opt($opt_key, $value)
	{
		$this->_conf[$this->group()][$opt_key] = $value;
	}
	
	//--------------------------------------------------------------------------
	
	public function opt($opt_key)
	{
		return $this->_conf[$this->group()][$opt_key];
	}
	
	//--------------------------------------------------------------------------
	
	public function set_group($group = 'default')
	{
		$this->_group = $group;
	}
	
	//--------------------------------------------------------------------------
	
	public function group()
	{
		return $this->_group;
	}
	
	//--------------------------------------------------------------------------
}