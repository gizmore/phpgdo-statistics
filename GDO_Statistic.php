<?php
namespace GDO\Statistics;

use GDO\Core\GDO;
use GDO\Core\Method;
use GDO\Date\Time;
use GDO\Core\GDT_String;
use GDO\Core\GDT_UInt;
use GDO\Date\GDT_Date;
use GDO\DB\Cache;
use GDO\Core\GDT_Enum;
use GDO\Core\Application;

/**
 * Statistics about called module methods each day.
 * 
 * @author gizmore
 * @version 7.0.1
 * @since 6.8.0
 */
final class GDO_Statistic extends GDO
{
	public function gdoEngine() : string { return GDO::MYISAM; }
	
	public function gdoCached() : bool { return false; }
	
	public function gdoColumns() : array
	{
	    return [
			GDT_Date::make('ph_day')->primary()->notNull(),
	        GDT_Enum::make('ph_type')->primary()->enumValues('GET', 'POST')->notNull(),
		    GDT_String::make('ph_module')->ascii()->caseS()->max(64)->primary(),
		    GDT_String::make('ph_method')->ascii()->caseS()->max(64)->primary(),
			GDT_UInt::make('ph_hits')->notNull()->initial('1'),
	    ];
	}
	
	public static function pagehit(Method $method)
	{
		$day = Time::getDateWithoutTime();
		$type = Application::$INSTANCE->verb;
		$mo = $method->getModuleName();
		$me = $method->getMethodName();
		try
		{
		    if ($row = self::table()->getById($day, $type, $mo, $me))
		    {
		        return $row->increase('ph_hits');
		    }
		    else
		    {
		        $row = self::table()->blank([
		            'ph_day' => $day,
		            'ph_type' => $type,
		            'ph_module' => $mo,
		            'ph_method' => $me,
		            'ph_hits' => '1',
		        ])->insert();
		    }
		}
		catch (\Throwable $ex)
		{
		    return self::table()->blank([
		        'ph_day' => $day,
		        'ph_type' => $type,
		        'ph_module' => $mo,
		        'ph_method' => $me,
		        'ph_hits' => '1',
		    ]);
		}
	}
	
	/**
	 * Return total hits for the whole time in universe.
	 * Caches the result.
	 * @return string
	 */
	public static function totalHits()
	{
	    static $hits;
	    if ($hits === null)
	    {
	        if (false === ($hits = Cache::get('statistics_hits')))
	        {
    	        $hits = self::table()->select('SUM(ph_hits)')->
    	           exec()->fetchValue();
    	        Cache::set('statistics_hits', $hits, 60);
	        }
	    }
		return $hits;
	}
	
	public static function todayHits()
	{
	    static $hits;
	    if ($hits === null)
	    {
	        if (false === ($hits = Cache::get('statistics_hits_today')))
	        {
	            $day = Time::getDateWithoutTime();
	            $hits = self::table()->select('SUM(ph_hits)')->
	               where("ph_day='{$day}'")->exec()->fetchValue();
	            Cache::set('statistics_hits_today', $hits);
	        }
	    }
	    return $hits;
	}
	
}
