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
 * Core plugin class.
 *
 * @category    Pimcore
 * @package     Plugin_Poll
 * @author      Rafał Gałka <rafal@modernweb.pl>
 * @copyright   Copyright (c) 2007-2011 ModernWeb (http://www.modernweb.pl)
 */
class Poll_Plugin extends Pimcore_API_Plugin_Abstract implements Pimcore_API_Plugin_Interface
{
    /**
     * @return string $statusMessage
     */
    public static function install()
    {
        $queries = array(
            'questions' =>
                'CREATE  TABLE IF NOT EXISTS `plugin_poll_questions` (
                    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
                    `title` VARCHAR(255) NOT NULL ,
                    `creationDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
                    `startDate` DATETIME NULL DEFAULT NULL ,
                    `endDate` DATETIME NULL DEFAULT NULL ,
                    `isActive` TINYINT(1)  NOT NULL DEFAULT 0 ,
                    `viewsCount` INT NOT NULL DEFAULT 0 ,
                    `multiple` TINYINT(1)  NOT NULL DEFAULT 0 ,
                    PRIMARY KEY (`id`) )
                ENGINE = InnoDB
                DEFAULT CHARACTER SET = utf8
                COLLATE = utf8_general_ci;',

            'answers' =>
                'CREATE  TABLE IF NOT EXISTS `plugin_poll_answers` (
                    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
                    `questionId` INT UNSIGNED NOT NULL ,
                    `title` VARCHAR(255) NOT NULL ,
                    `responses` INT NOT NULL DEFAULT 0 ,
                    `index` INT UNSIGNED NOT NULL DEFAULT 999999 ,
                    PRIMARY KEY (`id`) ,
                    INDEX `fk_plugin_poll_answers_plugin_poll_questions` (`questionId` ASC) ,
                    CONSTRAINT `fk_plugin_poll_answers_plugin_poll_questions`
                        FOREIGN KEY (`questionId` )
                        REFERENCES `plugin_poll_questions` (`id` )
                        ON DELETE CASCADE
                        ON UPDATE CASCADE)
                ENGINE = InnoDB
                DEFAULT CHARACTER SET = utf8
                COLLATE = utf8_general_ci;',
        );

        if (self::_executeQueries($queries)) {
            return "Poll Plugin successfully installed.";
        } else {
            return "Poll Plugin could not be installed. See debug log for more details.";
        }
    }

    /**
     * @return string $statusMessage
     */
    public static function uninstall()
    {
        $queries = array(
            'answers' => 'DROP TABLE IF EXISTS `plugin_poll_answers`;',
            'questions' => 'DROP TABLE IF EXISTS `plugin_poll_questions`;',
        );

        if (self::_executeQueries($queries)) {
            return "Poll Plugin successfully uninstalled.";
        } else {
            return "Poll Plugin could not be uninstalled. See debug log for more details.";
        }
    }

    /**
     * @return boolean $isInstalled
     */
    public static function isInstalled()
    {
        $result = true;
        try {
            Pimcore_API_Plugin_Abstract::getDb()->describeTable("plugin_poll_questions");
        } catch (Zend_Db_Exception $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * @return string
     */
    public static function getTranslationFileDirectory()
    {
        return PIMCORE_PLUGINS_PATH . "/Poll/static/texts";
    }

    /**
     * @param string $language
     * @return string path to the translation file relative to plugin direcory
     */
    public static function getTranslationFile($language)
    {
        if (is_file(self::getTranslationFileDirectory() . "/" . $language . ".csv")) {
            return "/Poll/static/texts/" . $language . ".csv";
        } else {
            return "/Poll/static/texts/en.csv";
        }
    }

    /**
     * Executes queries in single transaction.
     *
     * @param array $queries
     * @return boolean
     */
    protected static function _executeQueries(array $queries)
    {
        $db = Pimcore_API_Plugin_Abstract::getDb()->getResource();
        try {
            $db->beginTransaction();
            foreach ($queries as $query) {
                Pimcore_API_Plugin_Abstract::getDb()->query($query);
            }
            $db->commit();
            return true;
        } catch (Zend_Db_Exception $e) {
            $db->rollBack();
            logger::crit($e->getMessage());
            return false;
        }
    }

}
