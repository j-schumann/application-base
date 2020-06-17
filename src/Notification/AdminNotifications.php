<?php

/**
 * @copyright   (c) 2014-16, Vrok
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author      Jakob Schumann <schumann@vrok.de>
 */

namespace AppBase\Notification;

use DateTime;
use IntlDateFormatter;
use RuntimeException;
use SlmQueue\Worker\Event\ProcessJobEvent;
use SlmQueue\Worker\Event\WorkerEventInterface;
use Vrok\Service\Email as EmailService;
use Vrok\Service\UserManager;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;

/**
 * Listens to system events and notifies the administrators about them.
 *
 * dependencies: Vrok\Service\Email vhm, UserManager
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
     * @param EventManagerInterface $events
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $shared = $events->getSharedManager();

        $shared->attach(
            'AppBase\Controller\SlmQueueController',
            \AppBase\Controller\SlmQueueController::EVENT_BURIEDJOBSFOUND,
            [$this, 'onBuriedJobsFound'],
            $priority
        );

        $shared->attach(
            'AppBase\Controller\SlmQueueController',
            \AppBase\Controller\SlmQueueController::EVENT_LONGRUNNINGJOBSFOUND,
            [$this, 'onLongRunningJobsFound'],
            $priority
        );

        $shared->attach(
            'SupervisorControl\Controller\ConsoleController',
            // do not use constant to avoid dependency
            // (\SupervisorControl\Controller\ConsoleController::EVENT_PROCESSNOTRUNNING)
            'processNotRunning',
            [$this, 'onProcessNotRunning'],
            $priority
        );

        $shared->attach(
            'SlmQueue\Worker\WorkerInterface',
            WorkerEventInterface::EVENT_PROCESS_JOB,
            [$this, 'onProcessJobPost'],
            -100 // 100 = preProcess, -100 = postProcess
        );
    }

    /**
     * Sends a notification email to all queueAdmins reporting the number of buried
     * jobs remaining in the queue.
     *
     * @param \Zend\EventManager\EventInterface $e
     *
     * @throws RuntimeException when the queueAdmin group does not exist
     */
    public function onBuriedJobsFound(EventInterface $e)
    {
        $queue = $e->getTarget();
        $count = $e->getParam('count');

        $url     = $this->emailService->getViewHelperManager()->get('url');
        $fullUrl = $this->emailService->getViewHelperManager()->get('fullUrl');

        $mail = $this->emailService->createMail();
        $mail->setSubject('mail.slmQueue.buriedJobsFound.subject');

        $mail->setHtmlBody(['mail.slmQueue.buriedJobsFound.body', [
            'queueName' => $queue->getName(),
            'count'     => $count,
            'queueUrl'  => $fullUrl('https').$url('slm-queue/list-buried', [
                'name' => $queue->getName(),
            ]),
        ]]);

        $group = $this->userManager->getGroupRepository()
                ->findOneBy(['name' => 'queueAdmin']);
        if (! $group) {
            throw new RuntimeException(
                'Group "queueAdmin" not found when buried jobs where found!'
            );
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
        $fullUrl = $this->emailService->getViewHelperManager()->get('fullUrl');
        $durationFormat = $this->emailService->getViewHelperManager()->get('durationFormat');

        $mail = $this->emailService->createMail();
        $mail->setSubject('mail.slmQueue.longRunningJobsFound.subject');

        $mail->setHtmlBody(['mail.slmQueue.longRunningJobsFound.body', [
            'queueName' => $queue->getName(),
            'count'     => $count,
            'threshold' => $durationFormat(['seconds' => $threshold]),
            'queueUrl'  => $fullUrl('https').$url('slm-queue/list-running', [
                'name' => $queue->getName(),
            ]),
        ]]);

        $group = $this->userManager->getGroupRepository()
                ->findOneBy(['name' => 'queueAdmin']);

        if (! $group) {
            throw new RuntimeException(
                'Group "queueAdmin" not found when long running jobs where found!'
            );
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
        $fullUrl    = $this->emailService->getViewHelperManager()->get('fullUrl');
        $dateFormat = $this->emailService->getViewHelperManager()->get('DateFormat');

        $mail = $this->emailService->createMail();
        $mail->setSubject('mail.supervisor.processNotRunning.subject');

        $mail->setHtmlBody(['mail.supervisor.processNotRunning.body', [
            'processName'  => $processName,
            'processState' => $processInfo ? $processInfo['statename'] : 'NOT_FOUND',
            'now'          => $dateFormat(new DateTime(),
                    IntlDateFormatter::LONG, IntlDateFormatter::MEDIUM),
            'supervisorUrl' => $fullUrl('https').$url('supervisor'),
        ]]);

        $group = $this->userManager->getGroupRepository()
                ->findOneBy(['name' => 'supervisorAdmin']);
        if (! $group) {
            throw new RuntimeException(
                'Group "supervisorAdmin" not found when a process was not running!'
            );
        }

        $admins = $group->getMembers();
        foreach ($admins as $user) {
            $mail->addTo($user->getEmail(), $user->getDisplayName());
        }

        $this->emailService->sendMail($mail);
    }

    /**
     * Checks the result of each processed job. If it failed an email with
     * the saved message is sent to all queue admins.
     *
     * @param ProcessJobEvent $e
     */
    public function onProcessJobPost(ProcessJobEvent $e)
    {
        $result = $e->getResult();
        if ($result != ProcessJobEvent::JOB_STATUS_FAILURE) {
            return;
        }

        $queue = $e->getQueue();
        // There is no way to retrieve the message given to $queue->bury(),
        // not even with $queue->peek()
        $sql   = 'SELECT * FROM '.$queue->getOptions()->getTableName()
                .' WHERE id = ?';
        $jobs = $queue->connection->fetchAll($sql, [$e->getJob()->getId()]);
        $job = $jobs[0];
        $data = json_decode($job['data'], true);

        $url        = $this->emailService->getViewHelperManager()->get('url');
        $fullUrl    = $this->emailService->getViewHelperManager()->get('fullUrl');
        $dateFormat = $this->emailService->getViewHelperManager()->get('DateFormat');

        $mail = $this->emailService->createMail();
        $mail->setSubject('mail.slmQueue.jobFailed.subject');

        $mail->setHtmlBody(['mail.slmQueue.jobFailed.body', [
            'jobName'  => $data['metadata']['__name__'],
            'message'  => $job['message'],
            'now'      => $dateFormat(new DateTime(),
                            IntlDateFormatter::LONG, IntlDateFormatter::MEDIUM),
            'queueUrl' => $fullUrl('https').$url('slm-queue/list-buried', [
                'name' => $queue->getName(),
            ]),
        ]]);

        $group = $this->userManager->getGroupRepository()
                ->findOneBy(['name' => 'queueAdmin']);
        if (! $group) {
            throw new RuntimeException(
                'Group "queueAdmin" not found when a job failed!'
            );
        }

        $admins = $group->getMembers();
        foreach ($admins as $user) {
            $mail->addTo($user->getEmail(), $user->getDisplayName());
        }

        $this->emailService->sendMail($mail);
    }
}
