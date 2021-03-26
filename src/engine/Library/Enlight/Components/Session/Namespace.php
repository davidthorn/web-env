<?php
/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Enlight
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Enlight session namespace component.
 *
 * The Enlight_Components_Session_Namespace extends the Symfony Session with an easy array access.
 *
 *
 * @license     http://enlight.de/license     New BSD License
 */
class Enlight_Components_Session_Namespace extends Session implements ArrayAccess
{
    /**
     * Legacy wrapper
     *
     * @param string $name
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Legacy wrapper
     *
     * @param string $name
     */
    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    /**
     * Legacy wrapper
     *
     * @param string $name
     */
    public function __unset($name)
    {
        return $this->remove($name);
    }

    /**
     * Legacy wrapper
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        if (!$this->has($name)) {
            return false;
        }

        if ($this->get($name) === null) {
            return false;
        }

        return true;
    }

    /**
     * Whether an offset exists
     *
     * @param mixed $key a key to check for
     *
     * @return bool returns true on success or false on failure
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Unset the given offset.
     *
     * @param string $key key to unset
     */
    public function offsetUnset($key)
    {
        $this->remove($key);
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $key the offset to retrieve
     *
     * @return mixed can return all value types
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Offset to set
     *
     * @param mixed $key   the offset to assign the value to
     * @param mixed $value the value to set
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Clear session
     *
     * @deprecated since 5.7, and will be removed with 5.9. Use clear instead.
     */
    public function unsetAll()
    {
        trigger_error('Enlight_Components_Session_Namespace::unsetAll is deprecated since 5.7 and will be removed with 5.9. Use Enlight_Components_Session_Namespace::clear instead', E_USER_DEPRECATED);
        return $this->clear();
    }

    public function clear()
    {
        parent::clear();
        $this->set('sessionId', $this->getId());
    }
}
