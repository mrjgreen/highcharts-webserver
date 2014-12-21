<?php namespace Controllers;

class BaseController
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    public function __construct()
    {
        $this->request = app('request');
    }
}