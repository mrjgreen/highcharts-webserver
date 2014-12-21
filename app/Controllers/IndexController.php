<?php namespace Controllers;

use Aws\S3\S3Client;
use Controllers\Output\Local;
use Controllers\Output\S3;
use Services\Highcharts\HighchartsCreator;
use Services\Highcharts\InputConfig;
use Symfony\Component\HttpFoundation\RedirectResponse;

class IndexController extends BaseController
{
    /**
     * @return string|RedirectResponse
     * @throws \Exception
     */
    public function anyIndex()
    {
        if ($this->request->get('delete'))
        {
            return $this->deleteIndex();
        }

        $inputConfig = $this->getInputConfig();

        $filename = $inputConfig->getFilename();

        $output = $this->getOutputStrategy();

        if (!$output->exists($filename))
        {
            $creator = new HighchartsCreator();

            $tempFile = $creator->create($inputConfig);

            $output->write($tempFile, $filename);
        }

        $webFilePath = $output->getWebUrl($filename);

        return $this->request->get('noredirect') ? $webFilePath : new RedirectResponse($webFilePath);
    }

    /**
     * @return string
     */
    public function deleteIndex()
    {
        $filename = $this->getInputConfig()->getFilename();

        $output = $this->getOutputStrategy();

        return $output->delete($filename) ? '1 file deleted' : 'No file to delete';
    }

    /**
     * @return InputConfig
     */
    private function getInputConfig()
    {
        $input = $this->request->get('infile');

        return new InputConfig(
            $this->request->get('constr'),
            $this->request->get('useraw') ? $input : $this->fixUnquotedJson($input),
            $this->request->get('callback'),
            $this->request->get('width'),
            $this->request->get('scale', 2.5),
            $this->request->get('id'));
    }

    /**
     * @return Local|S3
     */
    private function getOutputStrategy()
    {
        if ($this->request->get('awskey')) {
            $client = S3Client::factory(array(
                'key' => $this->request->get('awskey'),
                'secret' => $this->request->get('awssecret'),
                'region' => $this->request->get('awsregion'),
            ));

            return new S3($client, $this->request->get('awsbucket'), $this->request->get('awsprefix'));
        }

        return new Local(app('paths')['public'], 'charts/');
    }

    /**
     * @param $jsonString
     * @return mixed
     */
    private function fixUnquotedJson($jsonString)
    {
        $validJson = preg_replace('/([{\[,])\s*([a-zA-Z0-9_]+?):/', '$1"$2":', $jsonString);

        $validJson = str_replace("'", '"', $validJson);

        return $validJson;
    }
}