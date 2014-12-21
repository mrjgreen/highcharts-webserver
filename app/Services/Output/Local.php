<?php namespace Controllers\Output;

class Local implements OutputInterface
{
    /**
     * @var string
     */
    private $outputDir;

    /**
     * @var string
     */
    private $subFolder;

    /**
     * @param $webDir
     * @param $subFolder
     */
    public function __construct($webDir, $subFolder)
    {
        $this->subFolder = '/' . trim($subFolder, '/') . '/';

        $this->outputDir = rtrim($webDir, '/') . $this->subFolder;
    }

    /**
     * @param $tempFile
     * @param $outputFile
     * @throws \Exception
     */
    public function write($tempFile, $outputFile)
    {
        $this->ensureOutputFolderIsWritable(dirname($this->outputDir . $outputFile));

        rename($tempFile, $this->outputDir . $outputFile);
    }

    /**
     * @param $outputFile
     * @return string
     */
    public function getWebUrl($outputFile)
    {
        return $this->subFolder . $outputFile;
    }

    /**
     * @param $outputFile
     * @return bool
     */
    public function exists($outputFile)
    {
        return is_file($this->outputDir . $outputFile);
    }

    /**
     * @param $outputFile
     * @return bool
     */
    public function delete($outputFile)
    {
        $file = $this->outputDir . $outputFile;

        if(is_file($file))
        {
            unlink($file);
            return true;
        }

        return false;
    }

    /**
     * @param $folder
     * @throws \Exception
     */
    private function ensureOutputFolderIsWritable($folder)
    {
        try{
            if(!is_writable($folder))
            {
                mkdir($folder, 0777, true) && chmod($folder, 0777);
            }
        }
        catch(\Exception $e)
        {
            throw new \Exception("The output folder $folder cannot be written to. Ensure the folder exists and is writable: mkdir $folder && chmod a+w $folder", 0, $e);
        }
    }
}