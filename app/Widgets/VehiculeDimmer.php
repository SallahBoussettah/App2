<?php

namespace App\Widgets;

use TCG\Voyager\Widgets\BaseDimmer;
use Arrilot\Widgets\AbstractWidget;
use App\Vehicule;

class VehiculeDimmer extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        $count = Vehicule::count();
        $string = 'Vehicule'.($count>1?'s':'');

        return view('voyager::dimmer', array_merge($this->config, [
            'icon'   => 'voyager-file-text',
            'title'  => "{$count} {$string}",
            'text'   => 'Vous avez '.$count.' enregistrées. Cliquez sur le bouton ci-dessous pour afficher toutes les vehicules.',
            'button' => [
                'text' => 'Voir toutes les vehicules',
                'link' => route('voyager.vehicules.index'),
            ],
            'color' => '#2ecc71',
        ]));
    }

    /**
     * Determine if the widget should be displayed.
     *
     * @return bool
     */
    public function shouldBeDisplayed()
    {
        return true;
    }
}
