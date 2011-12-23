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
class Poll_Answer_List extends Pimcore_Model_List_Abstract
    implements
        Zend_Paginator_Adapter_Interface,
        Zend_Paginator_AdapterAggregate,
        Iterator
{
    /**
     * @var array
     */
    public $answers = array();

    /**
     * @var array
     */
    public $validOrderKeys = array(
        'index',
    );

    /**
     * @param integer $questionId
     */
    public function __construct($questionId)
    {
        $this->setCondition("questionId = ?", array($questionId));
        $this->setOrderKey('index');
        $this->setOrder('asc');
    }

    /**
     * @param string $key
     * @return boolean
     */
    public function isValidOrderKey($key)
    {
        if (in_array($key, $this->validOrderKeys)) {
            return true;
        }
        return false;
    }

    /**
     * @param integer $offset
     * @param integer $itemCountPerPage
     * @return array
     */
    public function getItems($offset, $itemCountPerPage) {
        $this->setOffset($offset);
        $this->setLimit($itemCountPerPage);
        return $this->load();
    }

    /**
     * @return integer
     */
    public function count() {
        return $this->getTotalCount();
    }

    /**
     * @return Poll_Answer_List
     */
    public function getPaginatorAdapter() {
        return $this;
    }

    /**
     * @return array
     */
    public function getAnswers()
    {
        if (empty($this->answers)) {
            $this->load();
        }
        return $this->answers;
    }

    /**
     * @param array $answers
     * @return Poll_Answer_List
     */
    public function setAnswers(array $answers)
    {
        $this->answers = $answers;
        return $this;
    }

    /**
     * @param Poll_Answer $answer
     * @return Poll_Answer_List
     */
    public function append(Poll_Answer $answer)
    {
        $this->answers[] = $answer;
        return $this;
    }

    /**
     * @param integer $offset
     */
    public function offsetUnset($offset)
    {
        if(isset($this->answers[$offset])) {
            $this->answers[$offset]->delete();
            unset($this->answers[$offset]);
        }
    }

    /**
     * Methods for Iterator
     */
    public function rewind() {
        $this->getAnswers();
        reset($this->answers);
    }

    public function current() {
        $this->getAnswers();
        return current($this->answers);
    }

    public function key() {
        $this->getAnswers();
        return key($this->answers);
    }

    public function next() {
        $this->getAnswers();
        return next($this->answers);
    }

    public function valid() {
        $this->getAnswers();
        return $this->current() !== false;
    }

}
