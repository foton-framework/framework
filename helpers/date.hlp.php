<?php


class h_date
{

	public static function time_elapsed_string($ptime, $time = NULL) {
		if ( ! $time) $time = time();
	    $etime = $time - $ptime;
	    
	    if ($etime < 1) {
	        return '0 seconds';
	    }
	    
	    $a = array( 12 * 30 * 24 * 60 * 60  =>  'лет',
	                30 * 24 * 60 * 60       =>  'месяцев',
	                24 * 60 * 60            =>  'дней(я)',
	                60 * 60                 =>  'часов',
	                60                      =>  'минут',
	                1                       =>  'секунд'
	                );

	    foreach ($a as $secs => $str) {
	        $d = $etime / $secs;
	        if ($d >= 1) {
	            $r = round($d);
	            return $r . ' ' . $str;
	        }

	    }
	}

}