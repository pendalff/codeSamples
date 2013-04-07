<?php
/**
 * Abstract File Writer
 *
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: FileAbstract.php 20096 2010-01-06 02:05:09Z bkarwin $
 * @modifed   yapendalff@gmail.com
 */
class SMVC_Config_Writer_FileAbstract extends SMVC_Config_Writer_Base
{

    /**
     * Filename to write to
     *
     * @var string
     */
    protected $_filename = null;

    /**
     * Filename to write to
     *
     * @var string
     */
    protected $_section = null;
		
    /**
     * Wether to exclusively lock the file or not
     *
     * @var boolean
     */
    protected $_exclusiveLock = false;

    /**
     * Set the target filename
     *
     * @param  string $filename
     */
    public function setFilename($filename)
    {
        $this->_filename = $filename;

        return $this;
    }
	
    /**
     * Set wether to exclusively lock the file or not
     *
     * @param  boolean     $exclusiveLock
     * @return Config_Writer_Array
     */
    public function setExclusiveLock($exclusiveLock)
    {
        $this->_exclusiveLock = $exclusiveLock;

        return $this;
    }

    /**
     * Write configuration to file.
     *
     * @param string $filename
     * @param bool $exclusiveLock
     * @return void
     */
    public function write($filename = null, array $config = null, $section = null, $exclusiveLock = null)
    {
        if ($filename !== null) {
            $this->setFilename($filename);
        }
        if ($config !== null) {
            $this->setConfig($config);
        }
		if ($section !== null ){
			$this->setSection($section);
		}
        if ($exclusiveLock !== null) {
            $this->setExclusiveLock($exclusiveLock);
        }

        if ($this->_filename === null) {
            throw new SMVC_Config_Exception('No filename was set');
        }

        if ($this->_config === null) {
            throw new SMVC_Config_Exception('No config was set');
        }

        $configString = $this->render();

        $flags = 0;

        if ($this->_exclusiveLock) {
            $flags |= LOCK_EX;
        }

        $result = @file_put_contents($this->_filename, $configString, $flags);

        if ($result === false) {
            throw new SMVC_Config_Exception('Could not write to file "' . $this->_filename . '"');
        }
    }

    /**
     * Render a Zend_Config into a config file string.
     *
     * @since 1.10
     * @todo For 2.0 this should be redone into an abstract method.
     * @return string
     */
    public function render()
    {
        return "";
    }
}