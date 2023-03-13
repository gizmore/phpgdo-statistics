<?php
namespace GDO\Statistics\Test;

use GDO\Form\GDT_Form;
use GDO\Tests\TestCase;
use GDO\Statistics\GDO_Statistic;
use GDO\Core\Method\Impressum;
use function PHPUnit\Framework\assertEquals;
use GDO\Date\Time;

/**
 * @author gizmore
 */
final class StatisticTest extends TestCase
{
    public function testCounter()
    {
        GDO_Statistic::pagehit(Impressum::make());
        GDO_Statistic::pagehit(Impressum::make());
        $day = Time::getDateWithoutTime();
        $hits = GDO_Statistic::getById($day, GDT_Form::GET, 'Core', 'Impressum')->gdoVar('ph_hits');
        assertEquals(2, $hits, 'Test if page hits are counted correctly.');
    }

	public function testSimpleCounter()
	{
		GDO_Statistic::pagehitSimple();
		$hits = GDO_Statistic::simpleHitsTotal();
		self::assertGreaterThan(0, $hits, 'Test if simple page hits are counted.');
	}

}
