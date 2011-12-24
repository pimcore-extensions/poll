SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';


-- -----------------------------------------------------
-- Table `plugin_poll_questions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `plugin_poll_questions` ;

CREATE  TABLE IF NOT EXISTS `plugin_poll_questions` (
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
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `plugin_poll_answers`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `plugin_poll_answers` ;

CREATE  TABLE IF NOT EXISTS `plugin_poll_answers` (
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
COLLATE = utf8_general_ci;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
