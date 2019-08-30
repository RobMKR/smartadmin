<?php
namespace MgpLabs\SmartAdmin\Modules\Auditing;

use OwenIt\Auditing\Auditable as OwenAudit;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    use OwenAudit;

    /**
     * Remove All Params from url
     *
     * @param $url
     * @return string
     */
    protected function withoutParams($url){
        return strtok($url,'?');
    }

    /**
     * Override Auditable getCurrentRoute to avoid long url's insertion
     *
     * @return string
     */
    protected function getCurrentRoute(){
        if (App::runningInConsole()) {
            return 'console';
        }

        return $this->withoutParams(Request::fullUrl());
    }

}