<?php namespace Config;

class FileLoader implements LoaderInterface {


        /**
         * The default configuration path.
         *
         * @var string
         */
        protected $path;
        
        
        /**
         * Create a new file configuration loader.
         *
         * @param  array  $files
         * @param  string  $path
         * @return void
         */
        public function __construct($path)
        {
                $this->path = $path;
        }

        /**
         * Load the given configuration group.
         *
         * @param  string  $environment
         * @param  string  $group
         * @param  string  $namespace
         * @return array
         */
        public function load($environment, $group)
        {
            
            $buildPath = '';

            $items = array();

            foreach($this->parseEnvironment($environment) as $env)
            {

                $buildPath .= $env . DIRECTORY_SEPARATOR;
                
                $file = "{$this->path}{$buildPath}{$group}.php";

                // Loop through the directories down the environment name, checking for the environment specific 
                // configuration files which will be merged on top of the previous files arrays so that they get
                // precedence over them if we are currently in an environments setup.
                if(is_file($file))
                {
                    $items = $this->mergeEnvironment($items, $this->readFile($file));
                }
            }

            return $items ?: null;
        }
        
        /**
         * Read the file and parse it returning the read array
         * 
         * @param  string  $file
         * @return array
         */
        protected function readFile($file)
        {
            return include($file);
        }
        
        /**
         * Split the environment at dots or slashes creating an array of namespaces to look through
         * 
         * @param  string  $environment
         * @return array
         */
        protected function parseEnvironment($environment)
        {
            $environments = array_filter(preg_split('/(\/|\.)/', $environment));
            
            array_unshift($environments, '');
            
            return $environments;
        }

        /**
         * Merge the items in the given file into the items.
         *
         * @param  array   $items
         * @param  string  $file
         * @return array
         */
        protected function mergeEnvironment(array $items1, array $items2)
        {
                return array_replace_recursive($items1, $items2);
        }

}
