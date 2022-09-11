<?php
namespace GDO\Statistics\Test;

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
        $hits = GDO_Statistic::getById($day, 'GET', 'Core', 'Impressum')->getVar('ph_hits');
        assertEquals($hits, 2, 'Test if page hits are counted correctly.');
    }
    
}
