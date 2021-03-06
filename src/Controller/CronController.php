<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace AppBase\Controller;

use Vrok\Mvc\Controller\AbstractActionController;

/**
 * Console router to trigger the CRON events.
 * The console routes must be called by an actual cron script, the listeners should
 * execute short tasks directly or add their tasks as jobs to the job queue.
 */
class CronController extends AbstractActionController
{
    const EVENT_CRON_HOURLY  = 'cronHourly';
    const EVENT_CRON_DAILY   = 'cronDaily';
    const EVENT_CRON_MONTHLY = 'cronMonthly';

    /**
     * @triggers cronHourly
     */
    public function cronHourlyAction()
    {
        $this->getEventManager()->trigger(self::EVENT_CRON_HOURLY, $this);
        echo 'cronHourly finished: '.date('Y-m-d H:i:s')."\n";
    }

    /**
     * @triggers cronDaily
     */
    public function cronDailyAction()
    {
        $this->getEventManager()->trigger(self::EVENT_CRON_DAILY, $this);
        echo 'cronDaily finished: '.date('Y-m-d H:i:s')."\n";
    }

    /**
     * @triggers cronMonthly
     */
    public function cronMonthlyAction()
    {
        $this->getEventManager()->trigger(self::EVENT_CRON_MONTHLY, $this);
        echo 'cronMonthly finished: '.date('Y-m-d H:i:s')."\n";
    }
}
