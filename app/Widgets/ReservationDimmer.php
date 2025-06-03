<?php

namespace App\Widgets;

use TCG\Voyager\Widgets\BaseDimmer;
use Arrilot\Widgets\AbstractWidget;
use App\Reservation;

class ReservationDimmer extends AbstractWidget
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
        $count = Reservation::count();
        $string = 'Réservation'.($count>1?'s':'');

        return view('voyager::dimmer', array_merge($this->config, [
            'icon'   => 'voyager-file-text',
            'title'  => "{$count} {$string}",
            'text'   => 'Vous avez '.$count.' enregistrées. Cliquez sur le bouton ci-dessous pour afficher toutes les réservations.',
            'button' => [
                'text' => 'Voir toutes les réservations',
                'link' => route('voyager.reservations.index'),
            ],
            'color' => '#e74c3c',
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
