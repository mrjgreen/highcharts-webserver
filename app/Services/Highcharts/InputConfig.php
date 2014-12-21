<?php namespace Services\Highcharts;

class InputConfig
{
    const FILE_EXTENSION = '.png';

    const MAX_WIDTH = 10000;

    protected  static $constructors = array(
        //'map'           => 'Map',
        'stockchart'    => 'StockChart',
        'chart'         => 'Chart'
    );

    public $json;
    public $callback;
    public $width;
    public $scale;
    public $constructor;
    public $id;

    public function __construct($constructor, $inputJson, $callback, $width, $scale, $id)
    {
        if(!$inputJson || !($decoded = json_decode($inputJson)))
        {
            throw new \InvalidArgumentException("Please supply a valid input json");
        }

        $this->json = json_encode($decoded);

        $this->callback = $callback;

        $constructor = strtolower($constructor);

        $this->constructor = isset(self::$constructors[$constructor]) ? self::$constructors[$constructor] : 'Chart';

        $this->width = $this->prepareAndValidateWidth($width);

        $this->scale = $this->prepareAndValidateScale($scale);

        $this->id = $id;
    }

    public function getFilename()
    {
        if($this->id)
        {
            return sha1($this->id) . self::FILE_EXTENSION;
        }

        $hash = sha1(json_encode(array($this->callback, $this->json)));

        return $hash . '_' . strtolower($this->constructor) .  '_s' . $this->scale . '_w' . $this->width . self::FILE_EXTENSION;
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
}