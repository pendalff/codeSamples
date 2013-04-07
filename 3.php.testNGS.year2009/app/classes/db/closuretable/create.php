<?php

/**
 * Db_ClosureTable_Create
 * 
 * @desc table creation
 * @package Db
 * @author Thomas Schaefer
 * 
 */
class Db_ClosureTable_Create extends Db_ClosureTable_Base {

	/**
	 * init
	 * @desc prepare sql creation cascade<br/>
	 * 1 and hasDepth=true => getSqlTableCreationWithDepth<br/>
	 * 2 and hasDepth=true => getSqlTableCreationWithDepthAndWeight<br/>
	 * default and hasDepth=false => getSqlTableCreationWithoutDepth
	 * @param integer $mode
	 * @return $this
	 */
	public function init($mode=1) {

		$this->add('SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;');
		$this->add('SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;');
		$this->add('SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE=\'TRADITIONAL\'');
		$this->add(vsprintf('USE `%s`;', array($this->dbName)));
		$this->add(vsprintf('DROP TABLE IF EXISTS `%s`.`%s`;', array($this->dbName, $this->closureTable)));

		// load relation layout into query set
		$this->add($this->getTable($mode=1));

		$this->add('SET SQL_MODE=@OLD_SQL_MODE;');
		$this->add('SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;');
		$this->add('SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;');

		return $this;
	}

	/**
	 * getRenderedCreateTable
	 * @desc returns sql string layout by mode
	 * @param integer $mode
	 * @return string
	 */
	private function getRenderedCreateTable($mode=1){
		
		$data = array();
		$data[] = $this->dbName;
		$data[] = $this->closureTable;

		// fields
		$data[] = $this->getProperty("ancestor");
		$data[] = $this->getProperty("descendant");
		if($this->hasDepth) {
			$data[] = $this->getProperty("depth");
		}

		// primary key
		$data[] = $this->getProperty("ancestor");
		$data[] = $this->getProperty("descendant");
		// index
		$data[] = $this->getProperty("ancestor");
		$data[] = $this->getProperty("ancestor");
		$data[] = $this->getProperty("descendant");
		$data[] = $this->getProperty("descendant");

		// ancestor constraint
		$data[] = $this->getProperty("ancestor");
		// foreign key
		$data[] = $this->getProperty("ancestor");
		//reference
		$data[] = $this->dbName;
		$data[] = $this->foreignTable;
		$data[] = $this->foreignField;

		// descendant constraint
		$data[] = $this->getProperty("descendant");
		// foreign key
		$data[] = $this->getProperty("descendant");
		//reference
		$data[] = $this->dbName;
		$data[] = $this->foreignTable;
		$data[] = $this->foreignField;

		// receive adjacency relation layout
		if($this->hasDepth) {
			if($mode==2) {
				return vsprintf($this->getSqlTableCreationWithDepth(), $data);
			} else {
				return vsprintf($this->getSqlTableCreationWithDepthAndWeight(), $data);
			}
		} else {
			return vsprintf($this->getSqlTableCreationWithoutDepth(), $data);
		}
	}

	/**
	 *
	 * @return <type>
	 */
	private function getTable($mode=1) {
		return $this->getRenderedCreateTable($mode);
	}

	/**
	 * getSqlTableCreationWithDepth
	 * @desc get sql for unweighted adjacency relation
	 * @return string
	 */
	private function getSqlTableCreationWithDepth() {
		$sql = 'CREATE TABLE IF NOT EXISTS `%s`.`%s`
	(
		`%s` BIGINT UNSIGNED NOT NULL ,
		`%s` BIGINT UNSIGNED NOT NULL ,
		`%s` TINYINT(3) NOT NULL DEFAULT 0 ,
		PRIMARY KEY (`%s`, `%s`) ,
		INDEX `%s` (`%s` ASC) ,
		INDEX `%s` (`%s` ASC) ,
		CONSTRAINT `%s` FOREIGN KEY (`%s` ) REFERENCES `%s`.`%s` (`%s` ) ON DELETE CASCADE ON UPDATE CASCADE,
		CONSTRAINT `%s` FOREIGN KEY (`%s` ) REFERENCES `%s`.`%s` (`%s` ) ON DELETE CASCADE ON UPDATE CASCADE
	) ENGINE = InnoDB;';
		return $sql;
	}

	/**
	 * getSqlTableCreationWithoutDepth
	 * @desc basic adjacency relation
	 * @return string
	 */
	private function getSqlTableCreationWithoutDepth() {
		$sql = 'CREATE TABLE IF NOT EXISTS `%s`.`%s`
	(
		`%s` BIGINT UNSIGNED NOT NULL ,
		`%s` BIGINT UNSIGNED NOT NULL ,
		PRIMARY KEY (`%s`, `%s`) ,
		INDEX `%s` (`%s` ASC) ,
		INDEX `%s` (`%s` ASC) ,
		CONSTRAINT `%s` FOREIGN KEY (`%s` ) REFERENCES `%s`.`%s` (`%s` ) ON DELETE CASCADE ON UPDATE CASCADE,
		CONSTRAINT `%s` FOREIGN KEY (`%s` ) REFERENCES `%s`.`%s` (`%s` ) ON DELETE CASCADE ON UPDATE CASCADE
	) ENGINE = InnoDB;';
		return $sql;
	}

	/**
	 * getSqlTableCreationWithDepthAndWeight
	 *
	 * @desc get sql for weighted adjacency relation
	 * @return string
	 */
	private function getSqlTableCreationWithDepthAndWeight() {
		$sql = 'CREATE TABLE IF NOT EXISTS `%s`.`%s`
	(
		`%s` BIGINT UNSIGNED NOT NULL ,
		`%s` BIGINT UNSIGNED NOT NULL ,
		`%s` TINYINT(3) NOT NULL DEFAULT 0 ,
		`weight` tinyint(3) UNSIGNED NOT NULL DEFAULT \'1\',
		PRIMARY KEY (`%s`, `%s`) ,
		INDEX `%s` (`%s` ASC) ,
		INDEX `%s` (`%s` ASC) ,
		CONSTRAINT `%s` FOREIGN KEY (`%s` ) REFERENCES `%s`.`%s` (`%s` ) ON DELETE CASCADE ON UPDATE CASCADE,
		CONSTRAINT `%s` FOREIGN KEY (`%s` ) REFERENCES `%s`.`%s` (`%s` ) ON DELETE CASCADE ON UPDATE CASCADE
	) ENGINE = InnoDB;';
		return $sql;
	}


}