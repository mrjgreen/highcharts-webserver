<?php namespace Services\Highcharts;

class HighchartsCreator
{
    const PHANTOM_JS_BINARY = '/usr/local/bin/phantomjs';

    const HIGHCHARTS_CONVERT_BIN = '/srv/highcharts/exporting-server/phantomjs/highcharts-convert.js';

    public function create(InputConfig $inputConfig)
    {
        $outfilePath = $this->getTempFile() . '.' . $inputConfig->getExtension();

        $infileTmp = $this->getTempFile($inputConfig->json);

        $cmdArgs = "-infile $infileTmp -constr $inputConfig->constructor -scale $inputConfig->scale  -outfile $outfilePath";

        if($inputConfig->callback)
        {
            $callbackTmp = $this->getTempFile($inputConfig->callback);
            $cmdArgs .= ' -callback ' . $callbackTmp;
        }

        if($inputConfig->width)
        {
            $cmdArgs .= ' -width ' . $inputConfig->width;
        }

        try{
            $this->execute(self::PHANTOM_JS_BINARY . ' ' . self::HIGHCHARTS_CONVERT_BIN . ' ' . $cmdArgs);
        }
        catch (\Exception $e)
        {
            unlink($infileTmp);

            if($inputConfig->callback)
            {
                unlink($inputConfig->callback);
            }

            throw $e;
        }

        if(!is_file($outfilePath))
        {
            throw new \Exception("File $outfilePath could not be created.");
        }

        return $outfilePath;
    }

    private function getTempFile($contents = null)
    {
        $tmpFile = sys_get_temp_dir() . '/highchart' . uniqid();

        if(!is_null($contents)) file_put_contents($tmpFile, $contents);

        return $tmpFile;
    }

    private function execute($command)
    {
        $lastLine = exec($command, $output, $returnVar);

        if($returnVar)
        {
            throw new \Exception("Exec returned status: $returnVar with message: '$lastLine'");
        }
    }
}