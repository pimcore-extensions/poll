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
class Poll_Answer extends Pimcore_Model_Abstract
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var integer
     */
    public $questionId;

    /**
     * @var string
     */
    public $title;

    /**
     * @var integer
     */
    public $responses;

    /**
     * @var integer
     */
    public $index;

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
     * @return integer
     */
    public function getQuestionId()
    {
        return $this->questionId;
    }

    /**
     * @param integer $id
     * @return Poll_Question
     */
    public function setQuestionId($questionId)
    {
        $this->questionId = $questionId;
        return $this;
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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return integer
     */
    public function getResponses()
    {
        return $this->responses;
    }

    /**
     * @param integer $responses
     * @return Poll_Answer
     */
    public function setResponses($responses)
    {
        $this->responses = (int)$responses;
        return $this;
    }

    /**
     * @return integer
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @param integer $index
     * @return Poll_Answer
     */
    public function setIndex($index)
    {
        $this->index = (int)$index;
        return $this;
    }

    /**
     * @return Poll_Answer
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
     * @return Poll_Answer
     */
    public static function getById($id)
    {
        $answer = new self();
        $answer->getResource()->getById($id);
        return $answer;
    }

}
