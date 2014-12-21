<?php namespace Controllers\Output;

interface OutputInterface
{
    /**
     * @param $tempFile
     * @param $outputFile
     * @throws \Exception
     */
    public function write($tempFile, $outputFile);

    /**
     * @param $outputFile
     * @return string
     */
    public function getWebUrl($outputFile);

    /**
     * @param $outputFile
     * @return bool
     */
    public function exists($outputFile);

    /**
     * @param $outputFile
     * @return bool
     */
    public function delete($outputFile);
}