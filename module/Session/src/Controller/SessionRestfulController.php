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
use Exception;

class SessionRestfulController extends AbstractRestfulController
{
    use AdapterAwareTrait;
    
    public function notificationAction() 
    {
        $jsonview = new JsonModel();        
        
        $session_uuid = $this->params()->fromRoute('uuid',0);
        
        $response = new SessionResponse($this->adapter);
        
        $sql = new Sql($this->adapter);
        
        $select = new Select();
        $select
            ->columns(['UUID'])
            ->from('session_response')
            ->join('employees', 'employees.UUID = session_response.EMP_UUID', ['EMP_NUM', 'FNAME', 'LNAME'], Join::JOIN_INNER)
            ->join('users', 'users.USERNAME = employees.EMP_NUM', ['PHONE','EMAIL'], join::JOIN_INNER);
        
        $where = new Where();
        $where->equalTo('SESSION_UUID', $session_uuid);
        
        $active = new Where();
        $active->equalTo('session_response.STATUS', SessionResponse::INACTIVE_STATUS);
        $select->where($where)->where($active);
        
        $select->limit(1);
            
        $statement = $sql->prepareStatementForSqlObject($select);
        $resultSet = new ResultSet();
        try {
            $results = $statement->execute();
            $resultSet->initialize($results);
        } catch (Exception $e) {
            return [];
        }
        
        $records = $resultSet->toArray();
        foreach ($records as $record) {
            /****************************************
             * Notifications
             ****************************************/
            $view = new PhpRenderer();
            $settings = new SettingsModel($this->adapter);
            
            
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
            $message->setTo(sprintf('%s@vtext.com', $record['PHONE']));
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
                
                $response->read(['UUID' => $record['UUID']]);
                $response->STATUS = $response::ACTIVE_STATUS;
                $response->update();
            } catch (\Exception $e) {
                /**
                 * Log Error Information
                 */
            }
            
            $protocol->disconnect();
        }
        
        $session_response = new SessionResponse($this->adapter);
        $where = new Where();
        $where->equalTo('session_response.SESSION_UUID', $session_uuid);
        $records = $session_response->fetchAll($where);
        $jsonview->setVariable('total', count($records));
        
        $where->equalTo('STATUS', SessionResponse::INACTIVE_STATUS);
        $records = $session_response->fetchAll($where);
        $jsonview->setVariable('count', count($records));
        
        return $jsonview;
    }

    public function populateAction()
    {
        $session_uuid = $this->params()->fromRoute('uuid',0);
        
        $roster = new Roster($this->adapter);
        $current_roster = $roster->fetchAll();
        
        $response = new SessionResponse($this->adapter);
        $response->SESSION_UUID = $session_uuid;
        $response->STATUS = $response::INACTIVE_STATUS;
        
        foreach ($current_roster as $employee) {
            $response->UUID = $response->generate_uuid();
            $response->EMP_UUID = $employee['EMP_UUID'];
            $response->create();
        }
        
        return new JsonModel([
            'count' => 0,
            'total' => 100,
        ]);
    }
}