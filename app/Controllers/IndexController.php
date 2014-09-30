<?php

class IndexController extends BaseController
{
    const MAX_WIDTH = 10000;

    const PHANTOM_JS_BINARY = '/usr/local/bin/phantomjs';

    const HIGHCHARTS_CONVERT_BIN = '/srv/highcharts/exporting-server/phantomjs/highcharts-convert.js';

    public function anyIndex()
    {
        $input = $this->request->get('infile');

        $useRaw = $this->request->get('useraw');

        if(!$input || !($decoded = $useRaw ? json_decode($input) : $this->unquotedJsonDecode($input)))
        {
            throw new \InvalidArgumentException("Please supply a valid json_encoded 'infile' parameter");
        }

        $safeInput = json_encode($decoded);

        $callback = $this->request->get('callback');

        $type = strtolower($this->request->get('constr')) === 'stockchart' ? 'StockChart' : 'Chart';

        $width = $this->prepareAndValidateWidth($this->request->get('width'));

        $scale = $this->prepareAndValidateScale($this->request->get('scale', 2.5));

        if($id = $this->request->get('id'))
        {
            $filename = sha1($id) . '.png';
        }
        else
        {
            $filename = sha1(json_encode(array($callback, $safeInput))) . '_' . strtolower($type) .  '_s' . $scale . '_w' . $width . '.png';
        }

        $webFilePath = '/charts/' . $filename;

        $outfilePath = app('paths')['public'] . $webFilePath;

        if($this->request->get('delete'))
        {
            if(is_file($outfilePath))
            {
                unlink($outfilePath);

                return '1 file deleted';
            }

            return 'No file to delete';
        }

        if(!is_file($outfilePath))
        {
            $this->createFile($safeInput, $callback, $type, $scale, $width, $outfilePath);
        }

        return new \Symfony\Component\HttpFoundation\RedirectResponse($webFilePath);
    }

    private function prepareAndValidateWidth($width)
    {
        if(is_null($width))
        {
            return;
        }

        if((int)$width != $width || $width <= 0)
        {
            throw new \InvalidArgumentException("Please supply an integer width greater than 0");
        }

        if($width > self::MAX_WIDTH)
        {
            throw new \InvalidArgumentException("Please supply an integer width less than " . self::MAX_WIDTH);
        }

        return (int)$width;
    }

    private function prepareAndValidateScale($scale)
    {
        if(!is_numeric($scale) || $scale <= 0)
        {
            throw new \InvalidArgumentException("Please supply an numeric scale greater than 0");
        }

        return (float)$scale;
    }


    private function writeTempFile($contents)
    {
        $tmpfname = tempnam(sys_get_temp_dir(), "highchart");

        file_put_contents($tmpfname, $contents);

        return $tmpfname;
    }

    private function unquotedJsonDecode($s)
    {
        $valid_json = preg_replace('/([{\[,])\s*([a-zA-Z0-9_]+?):/', '$1"$2":', $s);

        $valid_json = str_replace("'", '"', $valid_json);

        return json_decode($valid_json);
    }

    private function createFile($safeInput, $callback, $type, $scale, $width, $outfilePath)
    {
        $this->ensureOutputFolderIsWritable(dirname($outfilePath));

        $infileTmp = $this->writeTempFile($safeInput);

        $cmdArgs = "-infile $infileTmp -constr $type -scale $scale  -outfile $outfilePath";

        if($callback)
        {
            $callbackTmp = $this->writeTempFile($callback);
            $cmdArgs .= ' -callback ' . $callbackTmp;
        }

        if($width)
        {
            $cmdArgs .= ' -width ' . $width;
        }

        try{
            $this->execute(self::PHANTOM_JS_BINARY . ' ' . self::HIGHCHARTS_CONVERT_BIN . ' ' . $cmdArgs);
        }
        catch (\Exception $e)
        {
            unlink($infileTmp);

            if($callback)
            {
                unlink($callbackTmp);
            }

            throw $e;
        }

        if(!is_file($outfilePath))
        {
            throw new \Exception("File $outfilePath could not be created.");
        }
    }

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

    private function execute($command)
    {
        $lastLine = exec($command, $output, $returnVar);

        if($returnVar)
        {
            throw new \Exception("Exec returned status: $returnVar with message: '$lastLine'");
        }
    }
}