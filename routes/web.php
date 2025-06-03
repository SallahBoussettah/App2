<?php



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


use GuzzleHttp\Client;

Route::get('/test-whatsapp', function () {
    $client = new Client();
    $response = $client->post('https://graph.facebook.com/v19.0/' . env('WHATSAPP_PHONE_ID') . '/messages', [
        'headers' => [
            'Authorization' => 'Bearer ' . env('WHATSAPP_TOKEN'),
            'Content-Type'  => 'application/json',
        ],
        'json' => [
            'messaging_product' => 'whatsapp',
            'to' => '212603145383', // Replace with your WhatsApp number
            'type' => 'text',
            'text' => ['body' => '✅ Test message sent from Laravel using Guzzle!'],
        ],
    ]);

    return json_decode($response->getBody(), true);
});



Route::get('/geturguide/reservations','HomeController@getReservations');
Route::get('/geturguide/bookings','HomeController@getBookings');

Route::get('/test-wa', 'ReservationController@sendMetaWhatsApp');
Route::post('/reservations/send-wa', 'ReservationController@sendWhatsApp')->name('reservations.send.whatsapp');


Route::post('/reservations/{id}/send-confirmation', 'ReservationController@sendBookingConfirmation')->name('reservation.sendConfirmation');
Route::post('/reservations/get_resa', 'ReservationController@getResa');
Route::post('/reservations/search', 'ReservationController@getSearch');
Route::get('/reservations/calendar','ReservationController@index_calendar')->name('voyager.reservations.calendar');
Route::get('/reservations/sendmultiemail','ReservationController@sendmultiemail');

Route::get('/reservations/imprimer/pancarte/{id}','ReservationController@imprimer_pancarte');
Route::get('/reservations/imprimer/liste/{with}/{date}/{type}/{chauffeur}/{ids_res}','ReservationController@imprimer_liste');

Route::post('reservations/edit_commercial','ReservationController@editCommercial');
Route::post('reservations/edit_chauffeur','ReservationController@editChauffeur')->name('Updatedriver');
Route::post('reservations/edit_status','ReservationController@editStatus')->name('Updatestatus');

Route::get('/statistiques','HomeController@index')->name('voyager.statics.index');
Route::post('/statistiques/get','HomeController@get');


Route::get('/chartbydrivers','HomeController@chartofdriver')->name('voyager.charts.index');
Route::post('/chartbydriver/chart','HomeController@getbydriver');


Route::get('/rapports','HomeController@index_rapports')->name('voyager.rapports.index');
Route::post('/rapports/get','HomeController@get_rapports');

Route::get('/archive-chauffeurs','HomeController@index_archived')->name('voyager.archived.index');
Route::get('/archive-chauffeurs/update/{id}','HomeController@update_archived');

Route::post('/paiements/status','HomeController@paiments_status');

Route::get('/accountants/download-zip/{ids_res}', 'HomeController@downloadZip');

/*Route::get('/paiments','HomeController@index_paiments')->name('voyager.paiments.index');
Route::post('/paiments/get','HomeController@get_paiments');*/

/*Gmail Api SouDeep*/


Route::get('/oauth/gmail', function (){
    return LaravelGmail::redirect();
});

Route::get('/oauth/gmail/callback', function (){
    //LaravelGmail::makeToken();
    LaravelGmail::setUserId("gmail-emails-422610")->makeToken();
    return redirect()->to('/getemails');
});

Route::get('/oauth/gmail/logout', function (){
    LaravelGmail::logout(); //It returns exception if fails
    return redirect()->to('/fails');
});
Route::get('/getemails','EmailsController@getemails');
Route::get('/getemailsnew','EmailsController@getemails_new');
Route::get('/refreshGmailToken','EmailsController@refreshGmailToken');


Voyager::routes();
