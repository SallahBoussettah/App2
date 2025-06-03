<?php

namespace App\Widgets;

use TCG\Voyager\Widgets\BaseDimmer;
use Arrilot\Widgets\AbstractWidget;
use App\Chauffeur;

class ChauffeurDimmer extends BaseDimmer
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
        $count = Chauffeur::count();
        $string = 'Chauffeur'.($count>1?'s':'');

        return view('voyager::dimmer', array_merge($this->config, [
            'icon'   => 'voyager-file-text',
            'title'  => "{$count} {$string}",
            'text'   => 'Vous avez '.$count.' enregistrées. Cliquez sur le bouton ci-dessous pour afficher toutes les chauffeurs.',
            'button' => [
                'text' => 'Voir toutes les chauffeurs',
                'link' => route('voyager.chauffeurs.index'),
            ],
            'color' => '#3498db',
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
