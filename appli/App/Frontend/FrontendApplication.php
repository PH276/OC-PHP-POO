<?php
namespace App\Frontend;

use \OCFram\Application;
use \OCFram\Cache;

class FrontendApplication extends Application
{
    public function __construct()
    {
        parent::__construct();

        $this->name = 'Frontend';
    }

    public function run()
    {
        $controller = $this->getController();
        $viewToCache = $controller->createCache();

        if  (array_key_exists($controller->view(), $viewToCache)){
            $cache = new Cache($controller->app(), '\\views\\' . $controller->app()->name() . '_' . $controller->module() . '_' . $controller->view());
            if ($cache->isValid()){
                $controller->page()->setContentView($cache->getContent());
            }else{
                $controller->execute();
                $controller->page()->genereView();
                $cache->setDate($viewToCache[$controller->view()] * Cache::SECONDES_JOUR);
                $cache->setContent($controller->page()->getContentView());
                $cache->genereCache();
            }

        }else
        {
            $controller->execute();
            $controller->page()->genereView();
        }

        $this->httpResponse->setPage($controller->page());
        $this->httpResponse->send();
    }
}