<?php

class Config implements \ArrayAccess, \Iterator
{
    /** @var array Array of configuration options */
    protected $config = [];
    /**
     * Class constructor, runs on object creation.
     *
     * @param mixed $context Raw array of configuration options or path to a
     *                       configuration file or directory
     */
    public function __construct(array $data)
    {
        $this->config = $data;
            
    }
    /**
     * Get all of the configuration items
     *
     * @return array
     */
    public function all()
    {
        return $this->config;
    }

    /**
     * Retrieve a configuration option via a provided key.
     *
     * @param string $key     Unique configuration option key
     * @param mixed  $default Default value to return if option does not exist
     *
     * @return mixed Stored config item or $default value
     */
    public function get(string $key, $default = null)
    {
        $config = $this->config;
        $keys = explode('.', $key);

        foreach ($keys as $k) {
            if (! isset($config[$k])) {
            //if (!is_array($config) || !array_key_exists($k, $config)) {
                return $default;
            }
            $config = $config[$k];
        }
        return $config;
    }

    /**
     * Store a config value with a specified key.
     *
     * @param string $key   Unique configuration option key
     * @param mixed  $value Config item value
     *
     * @return object This Config object
     */
    public function set(string $key, $value)
    {
        $config = &$this->config;
        $keys = explode('.', $key);

        foreach ($keys as $k) {
            $config = &$config[$k];
        }
        $config = $value;
    }

    /**
     * Check for the existance of a config item.
     *
     * @param string $key Unique configuration option key
     *
     * @return bool True if item existst, otherwise false
     */
    public function has(string $key): bool
    {
        $config = $this->config;
        $keys = explode('.', $key);

        foreach ($keys as $k) {
            if (! isset($config[$k])) {
//           	if (!is_array($config) || !array_key_exists($k, $config)) {
                return false;
            }
            $config = $config[$k];
        }
        return true;

        //return $this->get($key) !== null;
    }

    /**
     * ArrayAccess Methods
     */

    /**
     * Determine whether an item exists at a specific offset.
     *
     * @param int $offset Offset to check for existence
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        //return isset($this->config[$offset]);
        return $this->has($offset);
    }
    /**
     * Retrieve an item at a specific offset.
     *
     * @param int $offset Position of character to get
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        //return $this->config[$offset];
        return $this->get($offset);
    }
    /**
     * Assign a value to the specified item at a specific offset.
     *
     * @param mixed $offset The offset to assign the value to
     * @param mixed $value  The value to set
     */
    public function offsetSet($offset, $value)
    {
        //$this->config[$offset] = $value;
        $this->set($offset, $value);
    }
    /**
     * Unset an item at a specific offset.
     *
     * @param $offset The offset to unset
     */
    public function offsetUnset($offset)
    {
        //unset($this->config[$offset]);
        $this->set($offset, null);
    }


    /**
     * Iterator Methods
     */
    /**
     * Returns the config array element referenced by its internal cursor
     *
     * @return mixed The element referenced by the config array's internal cursor.
     *     If the array is empty or there is no element at the cursor, the
     *     function returns false. If the array is undefined, the function
     *     returns null
     */
    public function current()
    {
        return (is_array($this->config) ? current($this->config) : null);
    }
    /**
     * Returns the config array index referenced by its internal cursor
     *
     * @return mixed The index referenced by the config array's internal cursor.
     *     If the array is empty or undefined or there is no element at the
     *     cursor, the function returns null
     */
    public function key()
    {
        return (is_array($this->config) ? key($this->config) : null);
    }
    /**
     * Moves the config array's internal cursor forward one element
     *
     * @return mixed The element referenced by the config array's internal cursor
     *     after the move is completed. If there are no more elements in the
     *     array after the move, the function returns false. If the config array
     *     is undefined, the function returns null
     */
    public function next()
    {
        return (is_array($this->config) ? next($this->config) : null);
    }
    /**
     * Moves the config array's internal cursor to the first element
     *
     * @return mixed The element referenced by the config array's internal cursor
     *     after the move is completed. If the config array is empty, the function
     *     returns false. If the config array is undefined, the function returns
     *     null
     */
    public function rewind()
    {
        return (is_array($this->config) ? reset($this->config) : null);
    }
    /**
     * Tests whether the iterator's current index is valid
     *
     * @return bool True if the current index is valid; false otherwise
     */
    public function valid()
    {
        return (is_array($this->config) ? key($this->config) !== null : false);
    }
    /**
     * Remove a value using the offset as a key
     *
     * @param  string $key
     *
     * @return void
     */
    public function remove($key)
    {
        $this->offsetUnset($key);
    }

}
