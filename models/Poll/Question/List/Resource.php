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
class Poll_Question_List_Resource extends Pimcore_Model_List_Resource_Abstract
{
    /**
     * Loads a list of objects for the specicifies parameters,
     * returns an array of Object_Abstract elements.
     *
     * @return array
     */
    public function load()
    {
        $items = array();
        $itemsData = $this->db->fetchAll(sprintf("SELECT * FROM %s%s%s%s",
            Poll_Question_Resource::TABLE_NAME,
            $this->getCondition(),
            $this->getOrder(),
            $this->getOffsetLimit()
        ), $this->model->getConditionVariables());

        foreach ($itemsData as $data) {
            $item = new Poll_Question();
            $item->setValues($data);
            $items[] = $item;
        }

        $this->model->setQuestions($items);
        return $items;
    }

    /**
     * @return integer
     */
    public function getTotalCount()
    {
        return (int)$this->db->fetchOne(sprintf(
            "SELECT COUNT(*) as total FROM %s%s",
            Poll_Question_Resource::TABLE_NAME,
            $this->getCondition()
        ), $this->model->getConditionVariables());
    }
}
