<?php
/**
 * Copyright (c) 2012 Aleph Tav
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated 
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation 
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, 
 * and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO 
 * THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, 
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @author Aleph Tav <4lephtav@gmail.com>
 * @link http://www.4leph.com
 * @copyright Copyright &copy; 2012 Aleph Tav
 * @license http://www.opensource.org/licenses/MIT
 */

namespace Aleph\Cache;

use Aleph\Core;

/**
 * Base abstract class for building of classes that intended for caching different data.
 *
 * @abstract
 * @author Aleph Tav <4lephtav@gmail.com>
 * @version 1.0.0
 * @package aleph.cache
 */
abstract class Cache implements \ArrayAccess, \Countable
{
  /**
   * The vault key of all cached data.  
   *
   * @var string $vaultKey
   * @access private
   */
  private $vaultKey = null;
  
  /**
   * If this property equals TRUE saveKey and removeKey methods cannot be invoked.
   * 
   * @var boolean $lock
   * @access private
   */
  private $lock = false;
  
  /**
   * The vault lifetime. Defined as 1 year by default.
   *
   * @var integer $vaultLifeTime - given in seconds.
   * @access protected
   */
  protected $vaultLifeTime = 31536000; // 1 year
  
  /**
   * Constructor of the class.
   *
   * @access public
   */
  public function __construct()
  {
    $this->vaultKey = 'vault_' . Core\Aleph::getSiteUniqueID();
  }
  
  /**
   * Checks whether the current type of cache is available or not.
   *
   * @return boolean
   * @access public
   * @static
   */
  public static function isAvailable()
  {
    return true;
  }
 
  /**
   * Conserves some data identified by a key into cache.
   *
   * @param string $key - a data key.
   * @param mixed $content - some data.
   * @param integer $expire - cache lifetime (in seconds).
   * @access public
   * @abstract
   */
  abstract public function set($key, $content, $expire);

  /**
   * Returns some data previously conserved in cache.
   *
   * @param string $key - a data key.
   * @return mixed
   * @access public
   * @abstract
   */
  abstract public function get($key);

  /**
   * Removes some data identified by a key from cache.
   *
   * @param string $key - a data key.
   * @access public
   * @abstract
   */
  abstract public function remove($key);

  /**
   * Checks whether the cache lifetime is expired or not.
   *
   * @param string $key - a data key.
   * @return boolean
   * @access public
   * @abstract
   */
  abstract public function isExpired($key);

  /**
   * Removes all previously conserved data from cache.
   *
   * @access public
   * @abstract
   */
  abstract public function clean();
  
  /**
   * Returns the number of data items previously conserved in cache.
   *
   * @return integer
   * @access public
   */
  public function count()
  {
    $vault = $this->getVault();
    return is_array($vault) ? count($vault) : 0;
  }
  
  /**
   * Checks whether a data item exists.
   *
   * @param string $key - a data key to check for.
   * @return boolean
   * @access public
   */
  public function offsetExists($key)
	 {
	   return ($this->isExpired($key) === false);
	 }
  
  /**
   * Returns the previously conserved data from cache.
   *
   * @param string $key - a data key.
   * @return mixed
   * @access public
   */
	 public function offsetGet($key)
	 {
		  return $this->get($key);
	 }

  /**
   * Conserves some data in cache. If the method is firstly invoked the cache lifetime value equals the value of the property "vaultLifeTime".
   *
   * @param string $key - a data key.
   * @param mixed $content
   * @access public
   */
 	public function offsetSet($key, $content)
	 {
    $vault = $this->getVault();
    $hash = md5($key);
    $expire = isset($vault[$hash]) ? $vault[$hash] : $this->vaultLifeTime;
    $this->set($key, $content, $expire);
	 }

  /**
   * Removes a data item from cache.
   *
   * @param string $key - a data key.
   * @access public
   */
 	public function offsetUnset($key)
 	{
    $this->remove($key);
 	}
  
  /**
   * Returns the previously conserved data from cache.
   *
   * @param string $key - a data key.
   * @return mixed
   * @access public
   */
  public function __get($key)
  {
    return $this[$key];
  }
  
  /**
   * Conserves some data in cache. If the method is firstly invoked the cache lifetime value equals the value of the property "vaultLifeTime".
   *
   * @param string $key - a data key.
   * @param mixed $content
   * @access public
   */
  public function __set($key, $content)
  {
    $this[$key] = $content;
  }
  
  /**
   * Checks whether a data item exists.
   *
   * @param string $key - a data key to check for.
   * @return boolean
   * @access public
   */
  public function __isset($key)
  {
    return isset($this[$key]);
  }
  
  /**
   * Removes a data item from cache.
   *
   * @param string $key - a data key.
   * @access public
   */
  public function __unset($key)
  {
    unset($this[$key]);
  }
  
  /**
   * Returns the vault of data keys conserved in cache before.
   *
   * @return array - returns a key array or NULL if empty.
   * @access public
   */
  public function getVault()
  {
    return $this->get($this->vaultKey);
  }
  
  /**
   * Returns the vault lifetime.
   *
   * @return integer
   * @access public
   */
  public function getVaultLifeTime()
  {
    return $this->vaultLifeTime;
  }
  
  /**
   * Sets the vault lifetime.
   *
   * @param integer $vaultLifeTime - new vault lifetime in seconds.
   * @access public
   */
  public function setVaultLifeTime($vaultLifeTime)
  {
    $this->vaultLifeTime = abs((int)$vaultLifeTime);
  }
  
  /**
   * Saves the key of caching data in the key vault.
   *
   * @param string $key - a key to save.
   * @param integer $expire - cache lifetime of data defined by the key.
   * @access protected
   */
  protected function saveKey($key, $expire)
  {
    if ($this->lock) return;
    $this->lock = true;
    $vault = $this->getVault();
    $vault[md5($key)] = array($key, abs((int)$expire));
    $this->set($this->vaultKey, $vault, $this->vaultLifeTime);
    $this->lock = false;
  }
  
  /**
   * Removes the key from the key vault.
   *
   * @param string $hash - a previoulsy saved key of cached data.
   * @access protected
   */
  protected function removeKey($key)
  {
    if ($this->lock) return;
    $this->lock = true;
    $vault = $this->getVault();
    unset($vault[md5($key)]);
    $this->set($this->vaultKey, $vault, $this->vaultLifeTime);
    $this->lock = false;
  }
}