<?php

/**
 * @copyright   (c) 2014, Vrok
 * @license     http://customlicense CustomLicense
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace AppBase\Notification;

use Vrok\Service\Email as EmailService;
use Vrok\Service\UserManager;
use Zend\EventManager\EventInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;

/**
 * Listens to system events and notifies the administrators about them.
 *
 * depencies: Vrok\Service\Email vhm, UserManager
 */
class AdminNotifications implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    /**
     * @var EmailService
     */
    protected $emailService = null;

    /**
     * @var UserManager
     */
    protected $userManager = null;

    /**
     * Sets the ES instance to use.
     *
     * @param EmailService $es
     */
    public function setEmailService(EmailService $es)
    {
        $this->emailService = $es;
    }

    /**
     * Sets the UM instance to use.
     *
     * @param UserManager $um
     */
    public function setUserManager(UserManager $um)
    {
        $this->userManager = $um;
    }

    /**
     * Attaches to the shared eventmanager to listen for all events of interest for this
     * handler.
     *
     * @param \Zend\EventManager\EventManagerInterface $events
     */
    public function attach(\Zend\EventManager\EventManagerInterface $events)
    {
        $shared = $events->getSharedManager();

        $shared->attach(
            'AppBase\Controller\SlmQueueController',
            \AppBase\Controller\SlmQueueController::EVENT_BURIEDJOBSFOUND,
            [$this, 'onBuriedJobsFound']
        );

        $shared->attach(
            'AppBase\Controller\SlmQueueController',
            \AppBase\Controller\SlmQueueController::EVENT_LONGRUNNINGJOBSFOUND,
            [$this, 'onLongRunningJobsFound']
        );

        $shared->attach(
            'SupervisorControl\Controller\ConsoleController',
            // do not use constant to avoid dependency
            // (\SupervisorControl\Controller\ConsoleController::EVENT_PROCESSNOTRUNNING)
            'processNotRunning',

            [$this, 'onProcessNotRunning']
        );

        /*
         * Currently we can not detect the jobs process result:
         * @todo https://github.com/juriansluiman/SlmQueue/pull/83
        $shared->attach(
            'SlmQueue\Worker\WorkerInterface',
            \SlmQueue\Worker\WorkerEvent::EVENT_PROCESS_JOB_POST,
            [$this, 'onProcessJobPost']
        );*/
    }

    /**
     * Sends a notification email to all queueAdmins reporting the number of buried
     * jobs remaining in the queue.
     *
     * @param \Zend\EventManager\EventInterface $e
     *
     * @throws \RuntimeException when the queueAdmin group does not exist
     */
    public function onBuriedJobsFound(EventInterface $e)
    {
        $queue = $e->getTarget();
        $count = $e->getParam('count');

        $url     = $this->emailService->getViewHelperManager()->get('url');
        $fullUrl = $this->emailService->getViewHelperManager()->get('FullUrl');

        $mail = $this->emailService->createMail();
        $mail->setSubject('mail.slmQueue.buriedJobsFound.subject');

        $mail->setHtmlBody(['mail.slmQueue.buriedJobsFound.body', [
            'queueName' => $queue->getName(),
            'count'     => $count,
            'queueUrl'  => $fullUrl('https').$url('slm-queue/list-buried', [
                'name'  => $queue->getName(),
            ]),
        ]]);

        $group = $this->userManager->getGroupRepository()
                ->findOneBy(['name' => 'queueAdmin']);
        if (!$group) {
            throw new \RuntimeException(
                'Group "queueAdmin" not found when buried jobs where found!');
        }

        $admins = $group->getMembers();
        foreach ($admins as $user) {
            $mail->addTo($user->getEmail(), $user->getDisplayName());
        }

        $this->emailService->sendMail($mail);
    }

    /**
     * Sends a notification email to all queueAdmins reporting the number of long running
     * jobs remaining in the queue.
     *
     * @param \Zend\EventManager\EventInterface $e
     *
     * @throws \RuntimeException when the queueAdmin group does not exist
     */
    public function onLongRunningJobsFound(EventInterface $e)
    {
        $queue     = $e->getTarget();
        $count     = $e->getParam('count');
        $threshold = $e->getParam('threshold');

        $url     = $this->emailService->getViewHelperManager()->get('url');
        $fullUrl = $this->emailService->getViewHelperManager()->get('FullUrl');

        $mail = $this->emailService->createMail();
        $mail->setSubject('mail.slmQueue.longRunningJobsFound.subject');

        $mail->setHtmlBody(['mail.slmQueue.longRunningJobsFound.body', [
            'queueName' => $queue->getName(),
            'count'     => $count,
            'threshold' => $threshold / 60, // @todo implement DateInterval-Viewhelper
            'queueUrl'  => $fullUrl('https').$url('slm-queue/list-running', [
                'name'  => $queue->getName(),
            ]),
        ]]);

        $group = $this->userManager->getGroupRepository()
                ->findOneBy(['name' => 'queueAdmin']);

        if (!$group) {
            throw new \RuntimeException(
                'Group "queueAdmin" not found when buried jobs where found!');
        }

        $admins = $group->getMembers();
        foreach ($admins as $user) {
            $mail->addTo($user->getEmail(), $user->getDisplayName());
        }

        $this->emailService->sendMail($mail);
    }

    /**
     * Sends a notification to all supervisorAdmins when a process is not running.
     *
     * @param EventInterface $e
     */
    public function onProcessNotRunning(EventInterface $e)
    {
        $processName = $e->getParam('processName');
        $processInfo = $e->getParam('info');

        $url        = $this->emailService->getViewHelperManager()->get('url');
        $fullUrl    = $this->emailService->getViewHelperManager()->get('FullUrl');
        $dateFormat = $this->emailService->getViewHelperManager()->get('DateFormat');

        $mail = $this->emailService-->createMail();
        $mail->setSubject('mail.supervisor.processNotRunning.subject');

        $mail->setHtmlBody(['mail.supervisor.processNotRunning.body', [
            'processName'  => $processName,
            'processState' => $processInfo ? $processInfo['statename'] : 'NOT_FOUND',
            'now'          => $dateFormat(new \DateTime(),
                    \IntlDateFormatter::LONG, \IntlDateFormatter::MEDIUM),
            'supervisorUrl' => $fullUrl('https').$url('supervisor'),
        ]]);

        $group = $this->userManager->getGroupRepository()
                ->findOneBy(['name' => 'supervisorAdmin']);
        if (!$group) {
            throw new \RuntimeException(
                'Group "supervisorAdmin" not found when a process was not running!');
        }

        $admins = $group->getMembers();
        foreach ($admins as $user) {
            $mail->addTo($user->getEmail(), $user->getDisplayName());
        }

        $this->emailService-->sendMail($mail);
    }

    /**
     * @todo https://github.com/juriansluiman/SlmQueue/pull/104
     * @todo #250
     *
     * @param \Zend\EventManager\EventInterface $e
     */
    public function onProcessJobPost(EventInterface $e)
    {
        //  \Doctrine\Common\Util\Debug::dump($e, 4);
      //  \Doctrine\Common\Util\Debug::dump($e->getJob(), 4);
      //  $log = $this->serviceLocator->get('ZendLog');
        /* @var $log \Zend\Log\Logger */

      //  $log->debug(get_class($e));
      //  $log->debug(get_class($e->getTarget()));
      //  $log->debug(get_class($e->getParam('job')));
    }
}
