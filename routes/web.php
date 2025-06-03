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
use Illuminate\Http\Request;
use App\Reservation;

Route::get('/test-whatsapp', function (Request $request) {
    // Use Illuminate\Http\Request
    $client = new Client([
        'verify' => false // Disable SSL verification for local development
    ]);
    
    // Get the phone number from the request or use default test number
    $phone = $request->input('phone', '+212649224364');
    
    // Format the phone number if needed
    if (!strstr($phone, '+')) {
        if (substr($phone, 0, 1) !== '+') {
            $phone = '+' . $phone;
        }
    }
    
    // Default to hello_world template if no custom template is specified
    $template_name = $request->input('template', 'hello_world');
    $language_code = $request->input('language', 'en');
    
    // Build the request body - starting with the base request
    $requestBody = [
        'messaging_product' => 'whatsapp',
        'recipient_type' => 'individual',
        'to' => $phone,
        'type' => 'template',
        'template' => [
            'name' => $template_name,
            'language' => [
                'code' => $language_code
            ]
        ]
    ];
    
    // Special case for the default template
    if ($template_name === 'hello_world') {
        // The hello_world template doesn't need parameters
    } 
    // Special case for conf_template
    else if ($template_name === 'conf_template') {
        // Hardcoded parameters for testing
        $name = $request->input('name', 'John Smith');
        $date = $request->input('date', 'Monday, June 3, 2025');
        $time = $request->input('time', '14:30');
        $location = $request->input('location', 'Hotel Marrakech');
        
        $requestBody['template']['components'] = [
            [
                'type' => 'body',
                'parameters' => [
                    [
                        'type' => 'text',
                        'text' => $name
                    ],
                    [
                        'type' => 'text',
                        'text' => $date
                    ],
                    [
                        'type' => 'text',
                        'text' => $time
                    ],
                    [
                        'type' => 'text',
                        'text' => $location
                    ]
                ]
            ]
        ];
    }
    // General case for any other template with parameters
    else if ($request->has('params')) {
        $params = is_array($request->input('params')) 
            ? $request->input('params') 
            : json_decode($request->input('params'), true);
            
        if (!empty($params)) {
            $requestBody['template']['components'] = [
                [
                    'type' => 'body',
                    'parameters' => []
                ]
            ];
            
            // Add each parameter as a text component
            foreach ($params as $param) {
                $requestBody['template']['components'][0]['parameters'][] = [
                    'type' => 'text',
                    'text' => $param
                ];
            }
        }
    }
    
    try {
        $response = $client->post('https://graph.facebook.com/v19.0/' . env('WHATSAPP_PHONE_ID') . '/messages', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('WHATSAPP_TOKEN'),
                'Content-Type'  => 'application/json',
            ],
            'json' => $requestBody,
        ]);

        $result = json_decode($response->getBody(), true);
        
        // Add debug info
        $debug = [
            'phone_used' => $phone,
            'template_name' => $template_name,
            'language_code' => $language_code,
            'request_body' => $requestBody,
            'response_status' => $response->getStatusCode(),
            'response_body' => $result
        ];
        
        return [
            'success' => true,
            'data' => $result,
            'debug' => $debug
        ];
    } catch (\Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'error_trace' => $e->getTraceAsString(),
            'debug' => [
                'phone_used' => $phone,
                'template_name' => $template_name,
                'language_code' => $language_code,
                'request_body' => $requestBody
            ]
        ];
    }
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

Route::get('/test-reservation-template/{id}', function (Request $request, $id) {
    // Get the reservation
    $reservation = Reservation::findOrFail($id);
    
    // Format phone number if needed
    $phone = $request->input('phone', $reservation->client_telephone);
    if (!strstr($phone, '+')) {
        if (substr($phone, 0, 1) !== '+') {
            $phone = '+' . $phone;
        }
    }
    
    // Get template name or use default
    $template_name = $request->input('template', env('WHATSAPP_RESERVATION_TEMPLATE', 'hello_world'));
    $language_code = $request->input('language', 'en');
    
    $client = new Client([
        'verify' => false // Disable SSL verification for local development
    ]);
    
    // Format date for better readability
    $formattedDate = \Carbon\Carbon::parse($reservation->date_resa)->format('l, F j, Y');
    
    // Build the request body
    $requestBody = [
        'messaging_product' => 'whatsapp',
        'recipient_type' => 'individual',
        'to' => $phone,
        'type' => 'template',
        'template' => [
            'name' => $template_name,
            'language' => [
                'code' => $language_code
            ]
        ]
    ];
    
    // Only add parameters if we're not using the hello_world template
    if ($template_name !== 'hello_world') {
        $requestBody['template']['components'] = [
            [
                'type' => 'body',
                'parameters' => [
                    ['type' => 'text', 'text' => $reservation->client_nom],
                    ['type' => 'text', 'text' => $formattedDate],
                    ['type' => 'text', 'text' => $reservation->hour_start],
                    ['type' => 'text', 'text' => $reservation->hotel_nom]
                ]
            ]
        ];
    }
    
    try {
        $response = $client->post('https://graph.facebook.com/v19.0/' . env('WHATSAPP_PHONE_ID') . '/messages', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('WHATSAPP_TOKEN'),
                'Content-Type'  => 'application/json',
            ],
            'json' => $requestBody,
        ]);
    
        $result = json_decode($response->getBody(), true);
        
        // Add debug info
        $debug = [
            'reservation_id' => $id,
            'customer_name' => $reservation->client_nom,
            'date' => $formattedDate,
            'time' => $reservation->hour_start,
            'location' => $reservation->hotel_nom,
            'phone_used' => $phone,
            'template_name' => $template_name,
            'language_code' => $language_code,
            'request_body' => $requestBody,
            'response_status' => $response->getStatusCode(),
            'response_body' => $result
        ];
        
        return [
            'success' => true,
            'data' => $result,
            'debug' => $debug
        ];
    } catch (\Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'error_trace' => $e->getTraceAsString(),
            'debug' => [
                'reservation_id' => $id,
                'phone_used' => $phone,
                'template_name' => $template_name,
                'language_code' => $language_code,
                'request_body' => $requestBody
            ]
        ];
    }
});

// Simple test route to check exact template structure
Route::get('/debug-template', function (Request $request) {
    try {
        $client = new Client(['verify' => false]);
        
        // Get template details
        // Try different naming formats for the template
        $templateName = $request->input('template_name', 'conf_template');
        $templateFormat = $request->input('format', 'standard');
        $language = $request->input('language', 'en');
        $phone = $request->input('phone', '+212649224364');
        
        // Base message
        $message = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $phone,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $language
                ]
            ]
        ];
        
        // Add parameters based on format requested
        if ($templateFormat === 'standard') {
            // Standard format
            if ($templateName === 'conf_template' || $templateName === 'reservation_confirmation') {
                $message['template']['components'] = [
                    [
                        'type' => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => 'John Smith'],
                            ['type' => 'text', 'text' => 'Monday, June 3, 2025'],
                            ['type' => 'text', 'text' => '14:30'],
                            ['type' => 'text', 'text' => 'Hotel Marrakech']
                        ]
                    ]
                ];
            }
        } else if ($templateFormat === 'minimal') {
            // Minimal format - sometimes this works better
            if ($templateName !== 'hello_world') {
                $message['template']['components'] = [
                    [
                        'type' => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => 'John'],
                            ['type' => 'text', 'text' => 'Today'],
                            ['type' => 'text', 'text' => 'Now'],
                            ['type' => 'text', 'text' => 'Here']
                        ]
                    ]
                ];
            }
        } else if ($templateFormat === 'none') {
            // No parameters - try with just the template
        }
        
        // Try to fetch template info first if we're debugging
        $templateInfo = [];
        if ($request->has('check_template')) {
            try {
                $templateResponse = $client->get('https://graph.facebook.com/v19.0/' . env('WHATSAPP_PHONE_ID') . '/message_templates', [
                    'headers' => [
                        'Authorization' => 'Bearer ' . env('WHATSAPP_TOKEN'),
                        'Content-Type' => 'application/json',
                    ]
                ]);
                $templateInfo['templates'] = json_decode($templateResponse->getBody(), true);
            } catch (\Exception $e) {
                $templateInfo['templates_error'] = $e->getMessage();
            }
        }
        
        // Send the message
        $response = $client->post('https://graph.facebook.com/v19.0/' . env('WHATSAPP_PHONE_ID') . '/messages', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('WHATSAPP_TOKEN'),
                'Content-Type' => 'application/json',
            ],
            'json' => $message,
        ]);
        
        $result = json_decode($response->getBody(), true);
        
        return [
            'success' => true,
            'message' => $message,
            'response' => $result,
            'template_info' => $templateInfo
        ];
    } catch (\Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'request' => [
                'template' => $templateName ?? 'unknown',
                'language' => $language ?? 'unknown',
                'phone' => $phone ?? 'unknown',
                'format' => $templateFormat ?? 'unknown'
            ]
        ];
    }
});

// Test all template variations
Route::get('/try-all-templates', function (Request $request) {
    $client = new Client(['verify' => false]);
    $phone = $request->input('phone', '+212649224364');
    $language = $request->input('language', 'en');
    
    // Define variations to try
    $variations = [
        // Standard name
        ['name' => 'conf_template', 'language' => 'en'],
        ['name' => 'conf_template', 'language' => 'en_US'],
        ['name' => 'conf_template', 'language' => 'en_GB'],
        
        // Without underscore
        ['name' => 'conftemplate', 'language' => 'en'],
        ['name' => 'conftemplate', 'language' => 'en_US'],
        
        // With caps
        ['name' => 'Conf_Template', 'language' => 'en'],
        ['name' => 'CONF_TEMPLATE', 'language' => 'en'],
        
        // Test the hello_world template as reference
        ['name' => 'hello_world', 'language' => 'en'],
        ['name' => 'hello_world', 'language' => 'en_US'],
    ];
    
    $results = [];
    
    foreach ($variations as $variation) {
        $templateName = $variation['name'];
        $languageCode = $variation['language'];
        
        $message = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $phone,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $languageCode
                ]
            ]
        ];
        
        // Add parameters if not hello_world
        if ($templateName !== 'hello_world') {
            $message['template']['components'] = [
                [
                    'type' => 'body',
                    'parameters' => [
                        ['type' => 'text', 'text' => 'John Smith'],
                        ['type' => 'text', 'text' => 'Monday, June 3, 2025'],
                        ['type' => 'text', 'text' => '14:30'],
                        ['type' => 'text', 'text' => 'Hotel Marrakech']
                    ]
                ]
            ];
        }
        
        try {
            $response = $client->post('https://graph.facebook.com/v19.0/' . env('WHATSAPP_PHONE_ID') . '/messages', [
                'headers' => [
                    'Authorization' => 'Bearer ' . env('WHATSAPP_TOKEN'),
                    'Content-Type' => 'application/json',
                ],
                'json' => $message,
            ]);
            
            $result = json_decode($response->getBody(), true);
            $results[$templateName . '_' . $languageCode] = [
                'success' => true,
                'response' => $result
            ];
        } catch (\Exception $e) {
            $results[$templateName . '_' . $languageCode] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    return [
        'results' => $results,
        'test_variations' => $variations
    ];
});

// Test template with namespace
Route::get('/test-namespace-template', function (Request $request) {
    try {
        $client = new Client(['verify' => false]);
        
        // Get template details
        $phone = $request->input('phone', '+212649224364');
        
        // Get template info first
        $templateResponse = $client->get('https://graph.facebook.com/v19.0/' . env('WHATSAPP_PHONE_ID') . '/message_templates', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('WHATSAPP_TOKEN'),
                'Content-Type' => 'application/json',
            ]
        ]);
        
        $templateInfo = json_decode($templateResponse->getBody(), true);
        
        // Extract template data
        $templates = [];
        if (isset($templateInfo['data'])) {
            foreach ($templateInfo['data'] as $template) {
                $templates[] = [
                    'name' => $template['name'],
                    'status' => $template['status'] ?? 'unknown',
                    'category' => $template['category'] ?? 'unknown',
                    'language' => $template['language'] ?? 'unknown',
                    'components' => $template['components'] ?? [],
                    'namespace' => $template['namespace'] ?? null
                ];
            }
        }
        
        // Try to send a message with namespace if found
        $templateName = $request->input('template', 'hello_world');
        $namespace = null;
        $language = $request->input('language', 'en_US');
        
        // Find template and get its namespace
        foreach ($templates as $template) {
            if ($template['name'] === $templateName) {
                $namespace = $template['namespace'];
                break;
            }
        }
        
        $message = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $phone,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $language
                ]
            ]
        ];
        
        // Add namespace if found
        if ($namespace) {
            $message['template']['namespace'] = $namespace;
        }
        
        // Add parameters if needed and not hello_world
        if ($templateName !== 'hello_world') {
            $message['template']['components'] = [
                [
                    'type' => 'body',
                    'parameters' => [
                        ['type' => 'text', 'text' => 'John Smith'],
                        ['type' => 'text', 'text' => 'Monday, June 3, 2025'],
                        ['type' => 'text', 'text' => '14:30'],
                        ['type' => 'text', 'text' => 'Hotel Marrakech']
                    ]
                ]
            ];
        }
        
        // Send the message
        $response = $client->post('https://graph.facebook.com/v19.0/' . env('WHATSAPP_PHONE_ID') . '/messages', [
            'headers' => [
                'Authorization' => 'Bearer ' . env('WHATSAPP_TOKEN'),
                'Content-Type' => 'application/json',
            ],
            'json' => $message,
        ]);
        
        $result = json_decode($response->getBody(), true);
        
        return [
            'success' => true,
            'templates_found' => $templates,
            'message_sent' => $message,
            'response' => $result
        ];
    } catch (\Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'error_trace' => $e->getTraceAsString()
        ];
    }
});

Voyager::routes();
