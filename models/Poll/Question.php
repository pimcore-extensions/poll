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
 * @subpackage  Model
 * @author      Rafał Gałka <rafal@modernweb.pl>
 * @copyright   Copyright (c) 2007-2011 ModernWeb (http://www.modernweb.pl)
 * @license     http://www.modernweb.pl/license/new-bsd     New BSD License
 */

/**
 * @category    Pimcore
 * @package     Plugin_Poll
 * @subpackage  Model
 * @author      Rafał Gałka <rafal@modernweb.pl>
 * @copyright   Copyright (c) 2007-2011 ModernWeb (http://www.modernweb.pl)
 */
class Poll_Question extends Pimcore_Model_Abstract
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $creationDate;

    /**
     * @var string
     */
    public $startDate;

    /**
     * @var string
     */
    public $endDate;

    /**
     * @var boolean
     */
    public $isActive;

    /**
     * @var integer
     */
    public $viewsCount;

    /**
     * @var boolean
     */
    public $multiple;

    /**
     * @var Poll_Answer_List
     */
    public $answers;

    /**
     * @var Poll_Question
     */
    protected static $_current;

    /**
     * @var array
     */
    private $_answersToDelete = array();

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param integer $id
     * @return Poll_Question
     */
    public function setId($id)
    {
        $this->id = (int)$id;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Poll_Question
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param string $creationDate
     * @return Poll_Question
     */
    public function setCreationDate($creationDate)
    {
        if(empty($creationDate)) {
            $creationDate = null;
        }
        $this->creationDate = $creationDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param string $startDate
     * @return Poll_Question
     */
    public function setStartDate($startDate)
    {
        if(empty($startDate)) {
            $startDate = null;
        }
        $this->startDate = $startDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param string $endDate
     * @return Poll_Question
     */
    public function setEndDate($endDate)
    {
        if(empty($endDate)) {
            $endDate = null;
        }
        $this->endDate = $endDate;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @param boolean $isActive
     * @return Poll_Question
     */
    public function setIsActive($isActive)
    {
        $this->isActive = (bool)$isActive;
        return $this;
    }

    /**
     * @return integer
     */
    public function getViewsCount()
    {
        return $this->viewsCount;
    }

    /**
     * @param integer $viewsCount
     * @return Poll_Question
     */
    public function setViewsCount($viewsCount)
    {
        $this->viewsCount = (int)$viewsCount;
        return $this;
    }

    /**
     * @return Poll_Question
     */
    public function incrementViewsCount()
    {
        $this->viewsCount++;
        $this->save();
        return $this;
    }

    /**
     * @return boolean
     */
    public function getMultiple()
    {
        return $this->multiple;
    }

    /**
     * @param boolean $multiple
     * @return Poll_Question
     */
    public function setMultiple($multiple)
    {
        $this->multiple = (bool)$multiple;
        return $this;
    }

    /**
     * @return Poll_Answer_List
     */
    public function getAnswers()
    {
        if($this->answers === null) {
            $this->setAnswers(new Poll_Answer_List($this->getId()));
        }
        return $this->answers;
    }

    /**
     * @param Poll_Answer_List $answers
     * @return Poll_Question
     */
    public function setAnswers(Poll_Answer_List $answers)
    {
        $this->answers = $answers;
    }

    /**
     * Update answers from array.
     *
     * Method changes only state of the object you must
     * call save() if you want to push changes to database.
     *
     * @param array $answers
     * @return Poll_Question
     */
    public function updateAnswers(array $answers)
    {
        $update = array();
        $new = array();
        foreach($answers as $answer) {
            if(isset($answer['id'])) {
                unset($answer['responses']);
                $update[$answer['id']] = $answer;
            } else {
                $new[] = $answer;
            }
        }

        foreach($this->getAnswers() as $answer) {
            $id = $answer->getId();
            if(!isset($update[$id])) {
                $this->_answersToDelete[] = $id;
            } else {
                $answer->setValues($update[$id]);
            }
        }

        foreach($new as $data) {
            $answer = new Poll_Answer();
            $answer->setValues($data);
            $answer->setQuestionId($this->getId());
            $this->getAnswers()->append($answer);
        }

        return $this;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function getValue($key)
    {
        $getter = 'get'. ucfirst($key);
        if(method_exists($this, $getter)) {
            return $this->$getter();
        }
        return null;
    }

    /**
     * @return integer
     */
    public function sumResponses()
    {
        $sum = 0;
        foreach($this->getAnswers() as $answer) {
            $sum += (int)$answer->getResponses();
        }
        return $sum;
    }

    /**
     * @return Poll_Question_Resource
     */
    public function getResource()
    {
        if (!$this->resource) {
            $this->initResource("Poll_Question");
        }
        return $this->resource;
    }

    /**
     * @return Poll_Question
     */
    public function save()
    {
        if ($this->getId()) {
            $this->update();
        } else {
            $this->getResource()->create();
        }
        return $this;
    }

    /**
     * @param integer $id
     * @return Poll_Question
     */
    public static function getById($id)
    {
        $question = new self();
        $question->getResource()->getById($id);
        return $question;
    }

    /**
     * @return Poll_Question
     * @todo cache with expiration == endDate
     */
    public static function getCurrent()
    {
        if(null === self::$_current) {
            $now = date('Y-m-d H:i:s');
            $list = new Poll_Question_List();
            $list->setCondition(
                'isActive = 1 AND (' .
                    '(startDate <= ? AND endDate >= ?) OR ' .
                    '(startDate IS NULL AND endDate >= ?) OR ' .
                    '(startDate <= ? AND endDate IS NULL) OR ' .
                    '(startDate IS NULL AND endDate IS NULL)' .
                ')',
                array($now,$now,$now,$now)
            );
            $list->setLimit(1);
            $list->setOrderKey('id');
            $list->setOrder('ASC');

            self::$_current = $list->current();
        }
        return self::$_current;
    }

    /**
     * @return boolean
     */
    public static function hasCurrent()
    {
        return (self::getCurrent() instanceof Poll_Question);
    }

    /**
     * @return boolean
     */
    public function isCurrent()
    {
        $current = self::getCurrent();
        if(!$current instanceof self) {
            return false;
        }
        return ($this->getId() == $current->getId());
    }

    /**
     * Update question and answers.
     *
     * @return Poll_Question
     */
    protected function update()
    {
        $db = Pimcore_Resource::getConnection()->getResource();

        try {
            $db->beginTransaction();

            $answers = $this->getAnswers();
            foreach($answers as $key => $answer) {
                if(in_array($answer->getId(), $this->_answersToDelete)) {
                    $answers->offsetUnset($key);
                } else {
                    $answer->save();
                }
            }

            if(count($answers) < 2) {
                $this->setIsActive(false);
            }

            $this->getResource()->update();

            $db->commit();
        } catch(Exception $e) {
            $db->rollBack();
            throw $e;
        }

        return $this;
    }

}
