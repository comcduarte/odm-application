<?php
declare(strict_types=1);

namespace Session\Controller;

use Laminas\Db\Adapter\AdapterAwareTrait;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Db\Sql\Join;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\Sql\Where;
use Laminas\Mail\Protocol\Smtp as SmtpProtocol;
use Laminas\Mail\Transport\Smtp as SmtpTransport;
use Laminas\Mime\Mime;
use Laminas\Mvc\Controller\AbstractRestfulController;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;
use Laminas\View\Resolver\AggregateResolver;
use Roster\Model\Roster;
use Session\Model\SessionResponse;
use Settings\Model\SettingsModel;
use Twilio\Rest\Client;
use Exception;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Having;

class SessionRestfulController extends AbstractRestfulController
{
    use AdapterAwareTrait;
    
    public $twilio_sid;
    public $twilio_token;
    public $twilio_sender;
    
    public function notificationAction() 
    {
        $jsonview = new JsonModel();        
        
        $session_uuid = $this->params()->fromRoute('uuid',0);
        
        $sql = new Sql($this->adapter);
        
        $select = new Select();
        $select
            ->columns(['UUID'])
            ->from('session_response')
            ->join('employees', 'employees.UUID = session_response.EMP_UUID', ['EMP_NUM', 'FNAME', 'LNAME'], Join::JOIN_INNER)
            ->join('user_employee', 'user_employee.EMP_UUID = employees.UUID', [], join::JOIN_INNER)
            ->join('users', 'users.UUID= user_employee.USER_UUID', ['PHONE','EMAIL'], join::JOIN_INNER);
        
        $where = new Where();
            
        /**
         * Find specific Session UUID
         * @var \Laminas\Db\Sql\Where $session
         */
        $session = new Where();
        $session->equalTo('SESSION_UUID', $session_uuid);
        
        /**
         * Find only records that have registered phone numbers
         * @var \Laminas\Db\Sql\Where $phones
         */
        $phones = new Where();
        $phones->isNotNull('PHONE')->and->notEqualTo('PHONE', '');
        
        /**
         * Find all inactive records.
         * Inactive records indicate a notification was not sent yet.
         * @var \Laminas\Db\Sql\Where $active
         */
        $active = new Where();
        $active->equalTo('session_response.STATUS', SessionResponse::INACTIVE_STATUS);
        
        /**
         * Add all filters to predicate.
         */
        $where->addPredicates([$session, $active, $phones]);
        $select->where($where);
        
//         $select->limit(1);
            
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet();
        try {
            $results = $statement->execute();
            $resultSet->initialize($results);
        } catch (Exception $e) {
            return [];
        }
        
        $records = $resultSet->toArray();
        
        /**
         * Send out individual notification
         */
        $current = $records[0];
        $this->sendSMS($current['PHONE']);
        
        /**
         * Mark record as being sent
         */
        $response = new SessionResponse($this->adapter);
        $response->read(['UUID' => $current['UUID']]);
        $response->STATUS = $response::SENT_STATUS;
        $response->update();
        
        
        $jsonview->setVariable('records', $records);
        
        $total_records = $response->fetchAll($session);
        $jsonview->setVariable('total', count($total_records));
        
//         $where->equalTo('STATUS', SessionResponse::INACTIVE_STATUS);
//         $records = $session_response->fetchAll($where);
        $jsonview->setVariable('count', count($records));
        
        return $jsonview;
    }

    public function populateAction()
    {
        $session_uuid = $this->params()->fromRoute('uuid',0);
        
        $roster = new Roster($this->adapter);
        $complete_roster = $roster->fetchAll();
        
        $response = new SessionResponse($this->adapter);
        
        /**
           SELECT 
            `roster`.`EMP_UUID` AS `EMP_UUID`,
            COUNT(`session_response`.`EMP_UUID`) AS `COUNT`
           FROM `roster`
           LEFT JOIN `session_response` ON `session_response`.`EMP_UUID` = `roster`.`EMP_UUID`
           GROUP BY `roster`.`EMP_UUID`
           HAVING COUNT(`session_response`.`EMP_UUID`) = 0;
         */
        
        $session = new Where();
        $session->equalTo('session_response.SESSION_UUID', $session_uuid);
        
        
        $select = new Select();
        $select
            ->columns(['EMP_UUID', 'COUNT' => new Expression('COUNT(session_response.EMP_UUID)')])
            ->from($roster->getTableName())
            ->join($response->getTableName(), 'session_response.EMP_UUID = roster.EMP_UUID', [], Join::JOIN_LEFT)
            ->group('roster.EMP_UUID')
            ->having('COUNT(session_response.EMP_UUID) = 0')
            ->limit(1)
        ;
        $roster->setSelect($select);
        $current_roster = $roster->fetchAll();
        
        
        $response->SESSION_UUID = $session_uuid;
        $response->STATUS = $response::INACTIVE_STATUS;
        
        foreach ($current_roster as $employee) {
            $response->UUID = $response->generate_uuid();
            $response->EMP_UUID = $employee['EMP_UUID'];
            $response->create();
        }
        
        $count = $response->fetchAll($session);
        
        return new JsonModel([
            'count' => count($complete_roster) - count($count),
            'total' => count($complete_roster),
        ]);
    }
    
    private function sendEmail($to) 
    {
        /****************************************
         * Notifications
         ****************************************/
        $view = new PhpRenderer();
        $settings = new SettingsModel($this->adapter);
        $response = new SessionResponse($this->adapter);
        
        $resolver = new AggregateResolver();
        $view->setResolver($resolver);
        
        $map = new \Laminas\View\Resolver\TemplateMapResolver([
            'layout' => __DIR__ . '/../../view/session/layout/notification.phtml',
            'notifications/session' => __DIR__ . '/../../view/session/notifications/session.phtml',
        ]);
        $resolver->attach($map);
        
        $viewModel = new ViewModel();
        $viewModel->setTemplate('notifications/session');
        $view->viewModel()->setRoot($viewModel);
        
        $message = new \Laminas\Mail\Message();
        $body = new \Laminas\Mime\Message();
        
        $html = $view->render($viewModel);
        $part = new \Laminas\Mime\Part($html);
        $part->type = Mime::TYPE_HTML;
        
        $settings->read(['MODULE' => 'EMAIL', 'SETTING' => 'FROM']);
        $message->setFrom($settings->VALUE);
        $message->setTo($to);
        $message->setSubject(sprintf('PDH: Got Jobs?'));
        
        $body->addPart($part);
        
        $message->setBody($body);
        
        try {
            $settings->read(['MODULE' => 'EMAIL', 'SETTING' => 'SERVER']);
            $protocol = new SmtpProtocol($settings->VALUE);
            $protocol->connect();
            $settings->read(['MODULE' => 'EMAIL', 'SETTING' => 'HELO']);
            $protocol->helo($settings->VALUE);
            
            $transport = new SmtpTransport();
            $transport->setConnection($protocol);
            $protocol->rset();
            $transport->send($message);
            
            
        } catch (\Exception $e) {
            /**
             * Log Error Information
             */
        }
        
        $protocol->disconnect();
    }

    private function sendSMS($phone)
    {
        try {
            $twilio = new Client($this->twilio_sid, $this->twilio_token);
            $message = $twilio->messages->create($phone, [
                'body' => 'ODM: Got Jobs?',
                'from' => $this->twilio_sender,
            ]);
        } catch (Exception $e) {
            
        }
        
//         echo match($message->status) {
//             'accepted', 'scheduled', 'sent', 'queued', 'sending', 'delivered', 'received', 'receiving', 'read' => 'The SMS was sent successfully.',
//             default => 'Something went wrong sending the SMS'
//         };
    }
}