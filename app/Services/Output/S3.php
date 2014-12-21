<?php namespace Controllers\Output;

use Aws\S3\S3Client;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\AwsS3 as S3Adapter;

class S3 implements OutputInterface
{
    /**
     * @var Filesystem
     */
    private $s3Filesystem;

    /**
     * @var
     */
    private $awsBucket;

    /**
     * @var null
     */
    private $awsPrefix;

    /**
     * @param S3Client $s3Client
     * @param $awsBucket
     * @param null $awsPrefix
     */
    public function __construct(S3Client $s3Client, $awsBucket, $awsPrefix = null)
    {
        $this->awsBucket = $awsBucket;

        $this->awsPrefix = $awsPrefix;

        $this->s3Filesystem = new Filesystem(new S3Adapter($s3Client, $awsBucket, $awsPrefix));
    }

    /**
     * @param $tempFile
     * @param $outputFile
     */
    public function write($tempFile, $outputFile)
    {
        $this->s3Filesystem->write($outputFile, file_get_contents($tempFile), array(
            'visibility' => S3Adapter::VISIBILITY_PUBLIC,
        ));
    }

    /**
     * @param $outputFile
     * @return string
     */
    public function getWebUrl($outputFile)
    {
        return sprintf('http://%s.s3.amazonaws.com/%s%s', $this->awsBucket, $this->awsPrefix, $outputFile);
    }

    /**
     * @param $outputFile
     * @return bool
     */
    public function exists($outputFile)
    {
        return $this->s3Filesystem->has($outputFile);
    }

    /**
     * @param $outputFile
     * @return bool
     */
    public function delete($outputFile)
    {
        if($this->s3Filesystem->exists($outputFile))
        {
            $this->s3Filesystem->delete($outputFile);

            return true;
        }

        return false;
    }
}