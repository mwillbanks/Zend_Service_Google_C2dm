<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Zend_Service_Google
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * C2dm Message
 * Implements an individual message to a client
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Zend_Service_Google
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Google_C2dm_Message
{
    /**
     * @var string
     */
    protected $_registrationId;

    /**
     * @var string
     */
    protected $_collapseKey;

    /**
     * @var array
     */
    protected $_data = array();

    /**
     * Constructor
     *
     * @param string $registrationId
     * @param string $collapseKey
     * @param array  $data
     * @return Zend_Service_Google_C2dm_Message
     */
    public function __construct($registrationId=null, $collapseKey=null, array $data=array()) {
        $this->setRegistrationId($registrationId);
        $this->setCollapseKey($collapseKey);
        $this->setData($data);
    }


    /**
     * Get Registration ID
     *
     * @return string
     */
    public function getRegistrationId()
    {
        return $this->_registrationId;
    }

    /**
     * Set Registration ID
     *
     * @param string $registrationId
     * @return Zend_Service_Google_C2dm_Message
     * @throws Zend_Service_Google_C2dm_Exception
     */
    public function setRegistrationId($registrationId)
    {
        if (!is_string($registrationId)) {
            require_once 'Zend/Service/Google/C2dm/Exception.php';
            throw new Zend_Service_Google_C2dm_Exception('setRegistrationId() requires a string for registrationId');
        }
        $this->_registrationId = $registrationId;
        return $this;
    }

    /**
     * Get Collapse Key
     *
     * @return string
     */
    public function getCollapseKey()
    {
        return $this->_collapseKey;
    }

    /**
     * Set Collapse Key
     *
     * @param string $collapseKey
     * @return Zend_Service_Google_C2dm_Message
     * @throws Zend_Service_Google_C2dm_Exception
     */
    public function setCollapseKey($collapseKey)
    {
        if (!is_string($collapseKey)) {
            require_once 'Zend/Service/Google/C2dm/Exception.php';
            throw new Zend_Service_Google_C2dm_Exception('setCollapseKey() requires a string for collapseKey');
        }
        $this->_collapseKey = $collapseKey;
        return $this;
    }

    /**
     * Get Key Value Data Pairs
     *
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    /**
     * Set a Data Array
     *
     * @param array $data
     * @return Zend_Service_Google_C2dm_Message
     */
    public function setData(array $data)
    {
        $this->_data = $data;
    }

    /**
     * Validate the Message
     *
     * @return bool
     */
    public function validate()
    {
        if (empty($this->_registrationId)) {
            return false;
        } elseif (empty($this->_collapseKey)) {
            return false;
        } elseif (empty($this->_data)) {
            return false;
        }
        return true;
    }
}
