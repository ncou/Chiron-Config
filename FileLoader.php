<?php

class FileLoader {

	/**
     * @var array An array of paths to ultimately load the data from.
     */
    protected $paths = [];
    /**
     * Create the loader.
     * @param string|string[] $paths
     */
    public function __construct($paths)
    {
        if (!is_array($paths)) {
            $paths = array($paths);
        }
        $this->paths = $paths;
    }

    /**
     * Fetch and recursively merge in content from all file paths.
     *
     * @return array
     */
    public function load($skipMissing = false)
    {
        $result = [];
        foreach ($this->paths as $path) {
            $contents = $this->loadFile($path, $skipMissing);
            $result = array_replace_recursive($result, (array) $contents);
        }

        return $result;
    }
    /**
     * Fetch content from a single file path.
     *
     * @param string $path
     * @param bool $skipMissing True to ignore bad file paths.  If set to false, will throw an exception instead.
     * @return array
     * @throws FileNotFoundException
     */
    public function loadFile(string $path, bool $skipMissing = false)
    {
        if (!file_exists($path)) {
            if ($skipMissing) {
                return [];
            } else {
                throw new RuntimeException("The repository file '$path' could not be found.");
            }
        }
        // If the file exists but is not readable, we always throw an exception.
        if (!is_readable($path)) {
            throw new RuntimeException("The repository file '$path' exists, but it could not be read.");
        }

        return $this->parseFile($path);
    }


    /**
     * @return array
     */
    protected function parseFile(string $path)
    {
        $data = include $path;

        if (!is_array($data)) {
            throw new LogicException('File : (' . $path . ' does not return a valid array');
        }
        return $data;
    }


}
