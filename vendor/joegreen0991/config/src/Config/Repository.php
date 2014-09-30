<?php namespace Config;

use ArrayAccess;

class Repository implements ArrayAccess {

        /**
         * The loader implementation.
         *
         * @var \Ucli\Config\LoaderInterface
         */
        protected $loader;

        /**
         * The current environment.
         *
         * @var string
         */
        protected $environment;

        /**
         * All of the configuration items.
         *
         * @var array
         */
        protected $items = array();

        /**
         * Create a new configuration repository.
         *
         * @param  \Illuminate\Config\LoaderInterface  $loader
         * @param  string  $environment
         * @return void
         */
        public function __construct(LoaderInterface $loader, $environment = null)
        {
                $this->loader = $loader;
                $this->environment = $environment;
        }

        /**
         * Determine if the given configuration value exists.
         *
         * @param  string  $key
         * @return bool
         */
        public function has($key)
        {
                $default = microtime(true);
                
                return $this->get($key, $default) !== $default;
        }


        /**
         * Get the specified configuration value.
         *
         * @param  string  $key
         * @param  mixed   $default
         * @return mixed
         */
        public function get($key, $default = null)
        {
                list($group, $item) = $this->parseKey($key);

                $this->load($group);
                
                return $this->arrayGet($this->items, $key, $default);
        }

        /**
         * Set a given configuration value.
         *
         * @param  string  $key
         * @param  mixed   $value
         * @return void
         */
        public function set($key, $value)
        {
                list($group, $item) = $this->parseKey($key);

                // We'll need to go ahead and lazy load each configuration groups even when
                // we're just setting a configuration item so that the set item does not
                // get overwritten if a different item in the group is requested later.
                $this->load($group);

                if (is_null($item))
                {
                        $this->items[$group] = $value;
                }
                else
                {
                        $this->arraySet($this->items[$group], $item, $value);
                }
        }
        
        /**
         * Load the configuration group for the key.
         *
         * @param  string  $group
         * @param  string  $collection
         * @return void
         */
        protected function load($group)
        {
                // If we've already loaded this collection, we will just bail out since we do
                // not want to load it again. Once items are loaded a first time they will
                // stay kept in memory within this class and not loaded from disk again.
                if (isset($this->items[$group]))
                {
                        return;
                }
                
                $loaded = $this->loader->load($this->environment, $group);
                
                if($loaded){
                    $this->items[$group] = $loaded;
                }

                

        }

        

        /**
         * Get the collection identifier.
         *
         * @param  string  $group
         * @param  string  $namespace
         * @return string
         */
        protected function parseKey($key)
        {
            if(($pos = strpos($key, '.')) === false)
            {
                return array($key, null);  
            }
            
            return array(substr($key, 0, $pos),substr($key, $pos + 1));
        }
        
        
        /**
	 * Get an item from an array using "dot" notation.
	 *
	 * @param  array   $array
	 * @param  string  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	protected function arrayGet($array, $key, $default = null)
	{
		if (is_null($key)) return $array;

		if (isset($array[$key])) return $array[$key];

		foreach (explode('.', $key) as $segment)
		{
			if ( ! is_array($array) or ! array_key_exists($segment, $array))
			{
				return $default;
			}

			$array = $array[$segment];
		}

		return $array;
	}


	/**
	 * Set an array item to a given value using "dot" notation.
	 *
	 * If no key is given to the method, the entire array will be replaced.
	 *
	 * @param  array   $array
	 * @param  string  $key
	 * @param  mixed   $value
	 * @return array
	 */
	protected function arraySet(&$array, $key, $value)
	{
		if (is_null($key)) return $array = $value;

		$keys = explode('.', $key);

		while (count($keys) > 1)
		{
			$key = array_shift($keys);

			// If the key doesn't exist at this depth, we will just create an empty array
			// to hold the next value, allowing us to create the arrays to hold final
			// values at the correct depth. Then we'll keep digging into the array.
			if ( ! isset($array[$key]) or ! is_array($array[$key]))
			{
				$array[$key] = array();
			}

			$array =& $array[$key];
		}

		$array[array_shift($keys)] = $value;

		return $array;
	}

        /**
         * Get the loader implementation.
         *
         * @return \Illuminate\Config\LoaderInterface
         */
        public function getLoader()
        {
                return $this->loader;
        }

        /**
         * Set the loader implementation.
         *
         * @param \Illuminate\Config\LoaderInterface  $loader
         * @return void
         */
        public function setLoader(LoaderInterface $loader)
        {
                $this->loader = $loader;
        }

        /**
         * Get the current configuration environment.
         *
         * @return string
         */
        public function getEnvironment()
        {
                return $this->environment;
        }

        /**
         * Get all of the configuration items.
         *
         * @return array
         */
        public function getItems()
        {
                return $this->items;
        }

        /**
         * Determine if the given configuration option exists.
         *
         * @param  string  $key
         * @return bool
         */
        public function offsetExists($key)
        {
                return $this->has($key);
        }

        /**
         * Get a configuration option.
         *
         * @param  string  $key
         * @return mixed
         */
        public function offsetGet($key)
        {
                return $this->get($key);
        }

        /**
         * Set a configuration option.
         *
         * @param  string  $key
         * @param  mixed  $value
         * @return void
         */
        public function offsetSet($key, $value)
        {
                $this->set($key, $value);
        }

        /**
         * Unset a configuration option.
         *
         * @param  string  $key
         * @return void
         */
        public function offsetUnset($key)
        {
                $this->set($key, null);
        }

}