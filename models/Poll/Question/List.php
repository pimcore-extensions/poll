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
class Poll_Question_List extends Pimcore_Model_List_Abstract
    implements
        Zend_Paginator_Adapter_Interface,
        Zend_Paginator_AdapterAggregate,
        Iterator
{
    /**
     * @var array
     */
    public $questions = array();

    /**
     * @var array
     */
    public $validOrderKeys = array(
        'id',
        'title',
        'creationDate',
        'startDate',
        'endDate',
        'viewsCount',
    );

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
     * @return Poll_Question_List
     */
    public function getPaginatorAdapter() {
        return $this;
    }

    /**
     * @return array
     */
    public function getQuestions()
    {
        if (empty($this->questions)) {
            $this->load();
        }
        return $this->questions;
    }

    /**
     * @param array $questions
     * @return Poll_Question_List
     */
    public function setQuestions(array $questions)
    {
        $this->questions = $questions;
        return $this;
    }

    /**
     * Methods for Iterator
     */
    public function rewind() {
        $this->getQuestions();
        reset($this->questions);
    }

    public function current() {
        $this->getQuestions();
        return current($this->questions);
    }

    public function key() {
        $this->getQuestions();
        return key($this->questions);
    }

    public function next() {
        $this->getQuestions();
        return next($this->questions);
    }

    public function valid() {
        $this->getQuestions();
        return $this->current() !== false;
    }

}
