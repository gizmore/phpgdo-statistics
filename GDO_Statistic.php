<?php
namespace GDO\Statistics;

use GDO\Core\Application;
use GDO\Core\GDO;
use GDO\Core\GDT_Enum;
use GDO\Core\GDT_String;
use GDO\Core\GDT_UInt;
use GDO\Core\Method;
use GDO\Date\GDT_Date;
use GDO\Date\Time;
use GDO\DB\Cache;
use GDO\Form\GDT_Form;
use GDO\Util\FileUtil;

/**
 * Statistics about called module methods each day.
 *
 * @version 7.0.1
 * @since 6.8.0
 * @author gizmore
 */
final class GDO_Statistic extends GDO
{

	public static function pagehit(Method $method): self
	{
		$day = Time::getDateWithoutTime();
		$type = Application::$INSTANCE->verb;
		$mo = $method->getModuleName();
		$me = $method->getMethodName();
//		try
//		{
		if ($row = self::table()->getById($day, $type, $mo, $me))
		{
			return $row->increase('ph_hits');
		}
		else
		{
			return self::table()->blank([
				'ph_day' => $day,
				'ph_type' => $type,
				'ph_module' => $mo,
				'ph_method' => $me,
				'ph_hits' => '1',
			])->insert();
		}
//		}
//		catch (\Throwable $ex)
//		{
//		    return self::table()->blank([
//		        'ph_day' => $day,
//		        'ph_type' => $type,
//		        'ph_module' => $mo,
//		        'ph_method' => $me,
//		        'ph_hits' => '1',
//		    ]);
//		}
	}

	/**
	 * Return total hits for the whole time in universe.
	 * Caches the result.
	 */
	public static function totalHits(): int
	{
		static $hits;
		if (!isset($hits))
		{
			if (null === ($hits = Cache::get('statistics_hits')))
			{
				$hits = self::table()->select('SUM(ph_hits)')->
				exec()->fetchVar();
				$hits = $hits ?: 0;
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
			if (null === ($hits = Cache::get('statistics_hits_today')))
			{
				$day = Time::getDateWithoutTime();
				$hits = self::table()->select('SUM(ph_hits)')->
				where("ph_day='{$day}'")->exec()->fetchVar();
				$hits = $hits ?: 0;
				Cache::set('statistics_hits_today', $hits);
			}
		}
		return $hits;
	}

	##############
	### DB API ###
	##############

	public static function pagehitSimple(): void
	{
		self::pagehitSimpleB('_total_hits.txt');
		self::pagehitSimpleB(Time::getDate(0, 'Ymd') . '_hits.txt');
	}

	private static function pagehitSimpleB(string $filename): void
	{
		$mod = Module_Statistics::instance();
		$path = $mod->storagePath($filename);
		FileUtil::createFile($path);
		if ($fh = fopen($path, 'r+'))
		{
			$count = (int)fgets($fh);
			$count++;
			fseek($fh, 0);
			flock($fh, LOCK_EX);
			fwrite($fh, (string)$count);
			fwrite($fh, "\n");
			fclose($fh);
		}
	}

	public static function simpleHits(): array
	{
		return [
			self::simpleHitsTotal(),
			self::simpleHitsToday(),
		];
	}

	#######################
	### Simple File API ###
	#######################

	public static function simpleHitsTotal(): int
	{
		return self::simpleHit('_total_hits.txt');
	}

	private static function simpleHit(string $filename): int
	{
		$mod = Module_Statistics::instance();
		$path = $mod->storagePath($filename);
		return (int)@file_get_contents($path);
	}

	public static function simpleHitsToday(): int
	{
		return self::simpleHit(Time::getDate(0, 'Ymd') . '_hits.txt');
	}

	public function gdoEngine(): string { return GDO::MYISAM; }

	public function gdoCached(): bool { return false; }

	public function gdoColumns(): array
	{
		return [
			GDT_Date::make('ph_day')->primary()->notNull(),
			GDT_Enum::make('ph_type')->primary()->enumValues(GDT_Form::GET, GDT_Form::POST)->notNull(),
			GDT_String::make('ph_module')->ascii()->caseS()->max(64)->primary(),
			GDT_String::make('ph_method')->ascii()->caseS()->max(64)->primary(),
			GDT_UInt::make('ph_hits')->notNull()->initial('1'),
		];
	}

}
