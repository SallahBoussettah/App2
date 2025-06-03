<?php

namespace App\Actions;

use TCG\Voyager\Actions\AbstractAction;

class Showchart extends AbstractAction
{
    public function getTitle()
    {
        return 'Statistiques';
       // return 'Be First';
    }

    public function getIcon()
    {
        return 'voyager-pie-chart';
        //return 'voyager-eye';
    }

    public function getPolicy()
    {
        return 'read';
    }
    public function shouldActionDisplayOnDataType()
    {
        return $this->dataType->slug == 'chauffeurs';
    }

    public function getAttributes()
    {
        return [
            'class' => 'btn btn-sm btn-info pull-right',
        ];
    }

    public function getDefaultRoute()
    {
       // return route('befirst');
        return route('voyager.charts.index',['id' => $this->data->{'id'}]);
    }
}