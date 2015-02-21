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
class Poll_Question_Resource extends Pimcore_Model_Resource_Abstract
{
    const TABLE_NAME = 'plugin_poll_questions';

    /**
     * @var Pimcore_Resource_Wrapper
     */
    protected $db;

    /**
     * @var Poll_Question
     */
    protected $model;

    /**
     * Contains the valid database colums
     *
     * @var array
     */
    protected $_validColumns = array();

    /**
     * Get the valid database columns from database
     *
     */
    public function init()
    {
        $this->_validColumns = $this->getValidTableColumns(self::TABLE_NAME);
    }

    /**
     * Get the data for the object by the given id
     *
     * @param integer $id
     * @throws Exception
     */
    public function getById($id)
    {
        $select = new Zend_Db_Select($this->db->getResource());
        $data = $select
            ->from(self::TABLE_NAME)
            ->where('id = ?', $id)
            ->query()->fetch();

        if ($data && $data["id"] > 0) {
            $this->assignVariablesToModel($data);
        } else {
            throw new Exception("Question with ID '$id' doesn't exists");
        }
    }

    /**
     * Create a new record for the object in database,
     *
     */
    public function create()
    {
        $this->db->insert(self::TABLE_NAME, array(
            "title" => $this->model->getTitle(),
        ));
        $this->model->setId($this->db->lastInsertId());
    }

    /**
     * Save changes to database, it's an good idea to use save() instead
     *
     */
    public function update()
    {
        $type = get_object_vars($this->model);

        foreach ($type as $key => $value) {
            if (in_array($key, $this->_validColumns)) {
                $data[$key] = $value;
            }
        }

        $this->db->update(
            self::TABLE_NAME,
            $data,
            array('id = ?' => $this->model->getId())
        );
    }

    /**
     * Deletes object from database.
     *
     */
    public function delete()
    {
        $this->db->delete(self::TABLE_NAME, array('id = ?' => $this->model->getId()));
    }

}
