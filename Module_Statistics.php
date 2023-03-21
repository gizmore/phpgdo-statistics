<?php
namespace GDO\Statistics;

use GDO\Core\GDO_Module;
use GDO\Core\GDT_Checkbox;
use GDO\Core\Method;
use GDO\UI\GDT_Page;
use GDO\UI\GDT_Tooltip;

/**
 * Gather statistics about usage of modules and methods.
 *
 * @version 7.0.1
 * @since 6.8.0
 * @author gizmore
 */
final class Module_Statistics extends GDO_Module
{

	public function getConfig(): array
	{
		return [
			GDT_Checkbox::make('hook_sidebar')->initial('1'),
			GDT_Checkbox::make('extended_pagecount')->initial('0'),
		];
	}

	public function getClasses(): array
	{
		return [
			GDO_Statistic::class,
		];
	}

	public function onLoadLanguage(): void { $this->loadLanguage('lang/statistics'); }

	public function onInitSidebar(): void
	{
		if ($this->cfgBottomBar())
		{
			$bar = GDT_Page::$INSTANCE->bottomBar();
			if ($this->cfgExtended())
			{
				$total = GDO_Statistic::totalHits();
				$today = GDO_Statistic::todayHits();
			}
			else
			{
				[$total, $today] = GDO_Statistic::simpleHits();
			}
			$bar->addField(
				GDT_Tooltip::make()->icon('trophy')->
				tooltip('statistics_hitcounter', [$total, $today]));
		}
	}

	public function cfgBottomBar(): bool { return $this->getConfigValue('hook_sidebar'); }

	public function cfgExtended(): bool { return $this->getConfigValue('extended_pagecount'); }

	public function hookAfterExecute(Method $method)
	{
		if (!$method->isAjax())
		{
			if ($this->cfgExtended())
			{
				GDO_Statistic::pagehit($method);
			}
			else
			{
				GDO_Statistic::pagehitSimple();
			}
		}
	}

}
