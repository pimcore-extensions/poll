<?php

/**
 * ModernWeb
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.modernweb.pl/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kontakt@modernweb.pl so we can send you a copy immediately.
 *
 * @category    Pimcore
 * @package     Plugin_Poll
 * @author      Rafał Gałka <rafal@modernweb.pl>
 * @copyright   Copyright (c) 2007-2011 ModernWeb (http://www.modernweb.pl)
 * @license     http://www.modernweb.pl/license/new-bsd     New BSD License
 */

/**
 * @category    Pimcore
 * @package     Plugin_Poll
 * @author      Rafał Gałka <rafal@modernweb.pl>
 * @copyright   Copyright (c) 2007-2011 ModernWeb (http://www.modernweb.pl)
 */
class Poll_FrontendController extends Website_Controller_Action
{
    /**
     * @var Zend_Session_Namespace
     */
    protected $_session;

    public function init()
    {
        parent::init();

        $this->_session = new Zend_Session_Namespace('poll');
        if(null === $this->_session->poll) {
            $this->_session->poll = array();
        }
    }

    public function snippetAction()
    {
    }

    /**
     * Current active poll.
     *
     */
    public function currentAction()
    {
        $question = Poll_Question::getCurrent();
        if(!isset($this->_session->poll[$question->getId()])) {
            $question->incrementViewsCount();
        }
        $this->_session->poll[$question->getId()] = true;
        $this->view->voted = isset($_COOKIE['poll_' . $question->getId()]) ? 1 : 0;
        $this->view->question = $question;
    }

    /**
     * Poll response and results.
     *
     * @todo cookie expiration settings
     */
    public function responseAction()
    {
        $question = (int)$this->_getParam('question');
        if(!isset($this->_session->poll[$question])) {
            throw new Zend_Controller_Action_Exception('Hacking attempt?');
        }

        try {
            $question = Poll_Question::getById($question);
        } catch (Exception $e) {
            throw new Zend_Controller_Action_Exception('Question not exists?');
        }

        $thanks = false;

        if($this->_request->isPost()) {

            if(isset($_COOKIE['poll_' . $question->getId()])) {
                throw new Zend_Controller_Action_Exception('Question already voted');
            }

            $answers = $this->_request->getPost('answer');
            if(!is_array($answers) || empty($answers)){
                throw new Zend_Controller_Action_Exception('No response data provided');
            }

            foreach($question->getAnswers() as $answer) {
                if(in_array($answer->getId(), $answers)) {
                    $answer->responses++;
                    $answer->save();
                    if(!$question->getMultiple()) {
                        break;
                    }
                }
            }

            setcookie('poll_' . $question->getId(), 1, time()+60*60*24, '/');
            $thanks = true;
        }

        $this->view->question = $question;
        $this->view->thanks = $thanks;
        $this->_helper->json(array(
            'responses' => $this->view->render('frontend/responses.php')
        ));
    }

}
