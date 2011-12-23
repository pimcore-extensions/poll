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
class Poll_AdminController extends Pimcore_Controller_Action_Admin
{
    public function listAction()
    {
        // handle data change requests
        if($this->_getParam('data') && $this->_getParam('xaction')) {
            $refresh = false;

            $data = Zend_Json::decode($this->_getParam('data'));
            switch($this->_getParam('xaction')) {
                case 'update':
                    $question = Poll_Question::getById($data['id']);
                    unset($data['id']);
                    $question->setValues($data);
                    $question->save();
                    if(isset($data['isActive'])) {
                        $refresh = true;
                    }
                    break;
                case 'destroy':
                    $question = Poll_Question::getById($data);
                    $question->delete();
                    break;

            }
            $this->_helper->json(array('success' => true, 'refresh' => $refresh));
        }

        $list = new Poll_Question_List();

        $list->setOffset($this->_getParam("start"));
        $list->setLimit($this->_getParam("limit"));
        $list->setOrderKey("id");
        $list->setOrder("DESC");

        if($this->_getParam("filter")) {
            $list->setCondition("`title` LIKE ?", array("%{$this->_getParam("filter")}%"));
        }

        $list->load();

        $questions = array();
        foreach ($list as $question) {
            // @todo - optimization - single query via IN()
            $question->answers = iterator_to_array($question->getAnswers());
            $question->current = $question->isCurrent();
            $question->responses = $question->sumResponses();
            $questions[] = $question;
        }

        $this->_helper->json(array(
            'success' => true,
            'total' => $list->getTotalCount(),
            'data' => $questions,
        ));
    }

    public function addAction()
    {
        $question = $this->_getParam('question');
        $question = Zend_Filter::filterStatic($question, 'StripTags');
        $question = Zend_Filter::filterStatic($question, 'StringTrim');

        if (!empty($question)) {
            $poll = new Poll_Question();
            $poll->setTitle($question);
            $poll->save();
            $this->_helper->json(array("success" => true, 'id' => $poll->getId()));
        } else {
            logger::err("Poll_Plugin: Could not create poll, question must be defined");
            $this->_helper->json(array("success" => false));
        }
    }

    public function getAction()
    {
        $question = Poll_Question::getById((int)$this->_getParam('id'));

        $pollData = array(
            'responses' => $question->sumResponses(),
        );

        $allowedKeys = array(
            'id', 'title', 'startDate', 'endDate', 'isActive',
            'multiple', 'answers',
        );
        foreach (get_object_vars($question) as $key => $value) {
            if (in_array($key, $allowedKeys)) {
                $value = $question->getValue($key);
                if($value instanceof Iterator) {
                    $value = iterator_to_array($value);
                }
                switch($key) {
                    case 'startDate':
                    case 'endDate':
                        if($value) {
                            $value = strtotime($value);
                        }
                        break;
                }
                $pollData[$key] = $value;
            }
        }

        $pollData['shortTitle'] =
            Website_Tool_Text::cutStringRespectingWhitespace($question->getTitle(), 16);

        $this->_helper->json($pollData);
    }

    public function saveAction()
    {
        $question = Poll_Question::getById((int)$this->_getParam('id'));

        if (is_array($this->_request->getPost())) {
            $data = $this->_request->getPost();

            $question->updateAnswers(Zend_Json::decode($data['answers']));
            unset($data['answers']);

            foreach ($data as $key => $value) {
                $question->setValue($key, $value);
            }
        }

        try {
            $question->save();
            $this->_helper->json(array("success" => true));
        } catch (Exception $e) {
            Logger::log($e);
            $this->_helper->json(array("success" => false, "message" => $e->getMessage()));
        }
    }

}
