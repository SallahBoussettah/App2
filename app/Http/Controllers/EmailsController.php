<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use LaravelGmail;
use Illuminate\Support\Facades\Http;
use Dacastro4\LaravelGmail\Services\Message\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EmailsController extends Controller
{
    //

     public function getemails(){

            // Refresh token if necessary before making API requests
            $accessToken = $this->refreshGmailToken();
            //start code : soudeep
            $time = now();
           
            $messages = LaravelGmail::message()->from('do-not-reply@notification.getyourguide.com')->after($time->toDateString())->all();
           
            $now = new \DateTime();
            
            foreach ( $messages as $key => $message ) {
                    
                
                $messageId = $message->load()->getId();
                
                if (Reservation::where('message_id', $messageId)->exists()) {
                    Log::info('Email already processed. Skipping...'.$messageId);
                    continue;
                }
                
                $emailSubject = $message->load()->getSubject() ?? 'No subject';
                
                // Check if the subject starts with 'A booking has been canceled -'
                // Check if it's a cancellation email
                if (strpos($emailSubject, 'A booking has been canceled') === 0) {
                    $body = $message->load()->getHtmlBody();
                    $dom = new \DOMDocument();
                    $dom->loadHTML($body);
        
                    $cleanedBody = strip_tags($body);  // Remove all HTML tags from the body
    
                    // Now try to match the reference number from the cleaned body
                    preg_match('/Reference Number:\s*([A-Z0-9]+)/', $cleanedBody, $referenceMatches);
    
                    if (isset($referenceMatches[1])) {
                        $referenceNumber = trim($referenceMatches[1]);
                        if (substr($referenceNumber, -1) === 'N') {
                            $referenceNumber = substr($referenceNumber, 0, -1);  // Remove the last character
                        }
                       // dd($referenceNumber);
                        // Find the booking in the database and mark it as canceled
                        $booking = Reservation::where('ref_reservation', $referenceNumber)->first();
                        if ($booking) {
                            $booking->status = 'canceled';  // Assuming you have a status field
                            $booking->save();
                            Log::info('Booking canceled: ' . $referenceNumber);
                        } else {
                            Log::info('Booking with reference number not found: ' . $referenceNumber);
                        }
                    }
                } else if (strpos($emailSubject, 'Booking -') === 0) {
                        $body = $message->load()->getHtmlBody();
    
                        $dom = new \DOMDocument();
                        $dom->loadHTML($body);
                        
                        // Initialize an array to store the extracted data
                        $bookingData = [];
                        
                        // 1. Extract the Title
                        $titleNodes = $dom->getElementsByTagName('strong');
                        $bookingData['title'] = 'Unknown Title';
                        foreach ($titleNodes as $node) {
            
                            // Check for different possible patterns in the title
                            if (strpos($node->textContent, 'From Marrake') !== false ||  // Handle title format starting with "From Marrake"
                                strpos($node->textContent, 'Day Trip') !== false ||      // Handle title format with "Day Trip"
                                strpos($node->textContent, 'Marrakech') !== false) {     // Handle title format starting with "Marrakech"
                                
                                $bookingData['title'] = trim($node->textContent); // Store the title
                                break; // Exit loop once the title is found
                            }
                        }
                        // Alternative fallback if we still don't have a title (use regex or keywords as a fallback method)
                        if ($bookingData['title'] === 'Unknown Title') {
                            // Fallback method: Search for the title between "The following offer has been booked:" and "Option" or "Price"
                            preg_match('/The following offer has been booked:\s*(.*?)\s*(Option:|Most important data for this booking:)/', $body, $titleMatches);
                            
                            if (!empty($titleMatches[1])) {
                                
                                $bookingData['title'] = trim(strip_tags($titleMatches[1])); // Extract and store title
                            }
                        }
                        
                        // 2. Extract the Date and Time
                        $dateNodes = $dom->getElementsByTagName('p');
                        foreach ($dateNodes as $node) {
                            if (strpos($node->textContent, 'Date:') !== false) {
                                // Extract date and time separately
                                 preg_match('/Date:\s+([A-Za-z]+\s+\d{1,2},\s+\d{4})(?:\s+(\d{1,2}:\d{2}\s*[APM]*))?/', $node->textContent, $matches);
                                 
                            
                               // dd('g');
                                if (!empty($matches[1])) {
                                    
                                    $bookingData['date'] = $matches[1];
                                    $bookingData['time'] = $matches[2] ?? null;
                                }
                                break;
                            }
                        }
                        
                        // 3. Extract the Price
                        /*foreach ($dateNodes as $node) {
                            if (strpos($node->textContent, 'Price:') !== false) {
                                // Extract the price, remove "Price:" text
                                $rawPrice = trim(str_replace('Price:', '', $node->textContent));
                                $numericPrice = preg_replace('/[^0-9,.]/', '', $rawPrice);
                                // Remove commas for thousands separator and any leading period if it exists
                                $numericPrice = ltrim(str_replace(',', '', $numericPrice), '.');
                                // Convert to float and format to 2 decimal places
                                $bookingData['price'] = is_numeric($numericPrice) ? number_format((float)$numericPrice, 2, '.', '') : '0.00';

                                break;
                            }
                        }*/
                        
                        // 4. Extract the Reference Number
                        foreach ($dateNodes as $node) {
                            if (strpos($node->textContent, 'Reference number:') !== false) {
                                $bookingData['reference_number'] = trim(str_replace('Reference number:', '', $node->textContent));
                                break;
                            }
                        }
                        
                        
                        // 5. Extract the Number of Participants (shortened)
                        $valueToIdMap = [
                            47 => 4,
                            54 => 8,
                            65 => 15,
                            66 => 17,
                            41 => 20,
                        ];
                        foreach ($dateNodes as $node) {
                            if (strpos($node->textContent, 'Number of participants:') !== false) {
                                // Use regex to extract both group and individual participants
                                preg_match_all('/(\d+)\s+x\s+([\w\s]+)\s*\(.*?\)\s*\(د\.م\s*\d+\)/u', $node->textContent, $matches);
                        
                                $participants = [];
                                $adult = 0;
                                $child = 0;
                                $child3 = 0;
                                $group = 0;
                                $set = 0;
                                $groupId = null;
                                if (!empty($matches[1])) {
                                    foreach ($matches[1] as $index => $quantity) {
                                        $participantType = $matches[2][$index];
                                        
                                        // print_r($matches);
                                        // Check if the participant type includes "Group"
                                        if (strpos($participantType, 'Group') !== false) {
                                            // Handle group participants
                                            preg_match('/(\d+)\s*Persons/', $matches[0][0], $numPersons);
                                            preg_match('/(\d+)\s*Person/', $matches[0][0], $numPerson);
                                            
                                            preg_match('/Group up to (\d+)/', $participantType, $setgroupe);
                                            
                                            $participants[] = trim("$quantity x $participantType");
                                            $group = isset($numPersons[1]) ? $numPersons[1] : $numPerson[1];
                                            $set = $setgroupe[1];
                                            // Apply group-specific rule: Exact match with the map values
                                            $groupId = array_search($set, $valueToIdMap);

                                        }else{
                                            // Non-group participants (Adults, Seniors, Children)
                                            if(strpos($participantType, 'Seniors') !== false || strpos($participantType, 'Senior') !== false){
                                                $adult = $adult + $quantity;
                                                $participants[] = trim("$quantity x $participantType");
                                           
                                            }elseif(strpos($participantType, 'Adults') !== false || strpos($participantType, 'Adult') !== false){
                                                // Handle regular participants (Adults, Child)
                                                $adult = $adult + $quantity;
                                                $participants[] = trim("$quantity x $participantType");
                                               
                                            } elseif(strpos($participantType, 'Child') !== false || strpos($participantType, 'Children') !== false){
                                                $child = $quantity;
                                                $participants[] = trim("$quantity x $participantType");
                                               
                                            }else{
                                                $child3 = $quantity;
                                               
                                                $participants[] = trim("$quantity x $participantType");
                                            }
                                            // Increase the set total for non-group cases
                                            $set += $quantity;
                                        }

                                    }
                                    // Store the participants as a string in your database
                                    $bookingData['participants'] = implode(', ', $participants); // Example: "1 x Group up to 8, 2 x Adults, 1 x Child"
                                    // Apply non-group rule: Find the smallest matching or greater value
                                    if ($groupId === null) {
                                        foreach ($valueToIdMap as $id => $value) {
                                            if ($set <= $value) {
                                                $groupId = $id;
                                                break;
                                            }
                                        }
                                    }
                                }
                                break;
                            }
                        }
    
    
                        // 6. Extract the Customer Name and Phone Number
                        foreach ($dateNodes as $node) {
                            if (strpos($node->textContent, 'Main customer:') !== false) {
                                // Extract customer name (first line after 'Main customer:')
                                preg_match('/Main customer:\s+([^\n]+)/', $node->textContent, $nameMatches);
                                if ($nameMatches) {
                                    
                                    $bookingData['customer_name'] = trim(explode('customer',$nameMatches[1])[0]); // This will capture "Serina Bos"
                                }
                                
                                // Extract email address
                                preg_match('/customer-[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $node->textContent, $emailMatches);
                                if ($emailMatches) {
                                    $bookingData['email'] = trim($emailMatches[0]); // This will capture "customer-blqoxbp7yccp7rqi@reply.getyourguide.com"
                                }
        
                                // Extract phone number (after 'Phone:')
                                preg_match('/Phone:\s+([^\n]+)/', $node->textContent, $phoneMatches);
                                if ($phoneMatches) {
                                    $bookingData['phone_number'] = trim($phoneMatches[1]); // This will capture "+310630751065"
                                }
                                // Now, capture any text that comes after the phone number (the note)
                                $phonePosition = strpos($node->textContent, 'Phone:');
                                if ($phonePosition !== false) {
                                    // Get the text after the phone number
                                    $textAfterPhone = substr($node->textContent, $phonePosition);
                                    
                                    // Extract everything after the phone number (cleaned)
                                    $potentialNote = trim(str_replace('Phone: ' . $bookingData['phone_number'], '', $textAfterPhone));
                                    
                                    // Check if the note contains structured sections like "Tour language" or "Pickup location"
                                    // If it does, we ignore the note
                                    if (strpos($potentialNote, 'Tour language') === false && strpos($potentialNote, 'Pickup location') === false) {
                                        $bookingData['note'] = $potentialNote; // Save the note only if it does not contain other sections
                                    } else {
                                        $bookingData['note'] = null; // Set note as null if no additional note is found
                                    }
                                }
                                break;
                            }
                        }
            
                        
                        // 7. Extract the Tour Language
                        foreach ($dateNodes as $node) {
                            if (strpos($node->textContent, 'Tour language:') !== false) {
                                $bookingData['tour_language'] = trim(str_replace('Tour language:', '', $node->textContent));
                                break;
                            }
                        }
                        
                        // 8. Extract the Pickup Location
                        foreach ($dateNodes as $node) {
                            if (strpos($node->textContent, 'Pickup location:') !== false) {
                                $bookingData['pickup_location'] = trim(str_replace('Pickup location:', '', $node->textContent));
                                $bookingData['hotel_nom'] = explode(",", $bookingData['pickup_location'])[0];
                                break;
                            }

                        }
                        foreach ($dateNodes as $node) {
                            $bookingData['option'] = explode("Option:", $node->textContent)[1];
                            break;
                        }
                        
                       
                        
                        // At this point, $bookingData contains all the extracted information
                        
                        // Now insert into your database
                        // Assuming you have a Booking model
                        $reservation = new Reservation();
                            
                        $reservation->title = $bookingData['title'] ?? null;  // Use null if 'title' is not found
                        $reservation->gyginfos = 'Option: '.$bookingData['option'];  // Use null if 'title' is not found
                        $reservation->vehicule_id = $groupId;
                        $reservation->date_resa = Carbon::parse($bookingData['date'])->format('Y-m-d') ?? null;
                        $reservation->hour_start = $bookingData['time'] != '' ? Carbon::parse($bookingData['time'])->format('H:i:s') : '00:00';
                        $reservation->prixtotal = 0;
                        $reservation->pax_adulte = $adult > 0 ? $adult : $group;
                        $reservation->pax_enfant12 = $child;
                        $reservation->pax_enfant3 = $child3;
                        $reservation->client_email = $bookingData['email'] ?? null;
                        $reservation->ref_reservation = $bookingData['reference_number'] ?? 'GYG miss';
                        $reservation->participants = $bookingData['participants'] ?? null;
                        $reservation->client_nom = $bookingData['customer_name'] ?? 'GYG miss';
                        $reservation->client_telephone = $bookingData['phone_number'] ?? null;
                        $reservation->hotel_nom = $bookingData['hotel_nom'] ?? null;
                        $reservation->hotel_adresse = $bookingData['pickup_location'] ?? null;
                        $reservation->vol_cmt = $bookingData['note'] ?? null;
                        $reservation->message_id = $messageId ?? null;
                        $reservation->subject = $emailSubject;// Ensure this is unique
                        $reservation->agence_id = 30;
                        $reservation->devise_id=1;
                        $reservation->status='gmail';
                        
                        $reservation->save();
                     }
                
            //end
            }
            echo 'end - success'.$time->toDateTimeString();
        
    }
    
    
    
    
    
    
    
     public function getemails_new()
        {
            try {
                $accessToken = $this->refreshGmailToken(); 
                $time = now();
        
                $messages = LaravelGmail::message()
                    ->preload()
                    ->from('servicetransfersrak@gmail.com')
                     ->after($time->toDateString())
                    ->all(); 
        
                $now = new \DateTime();
        
                foreach ($messages as $message) {
                    try {
                        $messageId = $message->load()->getId();
                        $emailSubject = $message->load()->getSubject() ?? 'No subject';
        //  if (strpos($emailSubject, 'Fwd: Cancellation') === 0) {
        //      dd('ok');
        //  }
                        if (DB::connection('second_db')->table('reservations')->where('message_id', $messageId)->exists()) {
                            \Log::info('Email already processed. Skipping... ' . $messageId);
                            continue;
                        }
        
                        $attachments = $message->getAttachments();
        
                        foreach ($attachments as $attachment) {
                            try {
                                $filename = $attachment->getFilename();
        
                                if (strpos($filename, '.json') !== false) {
                                    $content = $attachment->getData();
                                    $json_string = base64_decode($content);
        
                                    $json_data = json_decode($json_string, true);
        
                                    if (json_last_error() !== JSON_ERROR_NONE || !$json_data) {
                                        \Log::warning("Invalid JSON in attachment for message: $messageId");
                                        continue;
                                    }
        
                                    $reservationData = $this->mapJsonToReservation($json_data, $messageId, $emailSubject);
                                    $existing = DB::connection('second_db')->table('reservations')
                                                ->where('ref_reservation', $reservationData['ref_reservation'])
                                                ->first();
                                                
                                                if ($existing) {
                                                   
                                                if (strpos($emailSubject, 'Fwd: Cancellation')=== 0) {
                                                     
                                                    DB::connection('second_db')->table('reservations')
                                                        ->where('id', $existing->id)
                                                        ->update(['status' => 'canceled']);
                                            
                                                    \Log::info("Reservation updated to canceled for message: $messageId");
                                                } else {
                                                    \Log::info("Reservation already exists. Skipping insert for message: $messageId");
                                                }
                                            }else {
                                                // New reservation, insert it
                                                $reservationId = DB::connection('second_db')->table('reservations')->insertGetId($reservationData);
                                                
                                                if (strpos($emailSubject, 'Fwd: Cancellation')=== 0) {
                                                    DB::connection('second_db')->table('reservations')
                                                        ->where('id', $reservationId)
                                                        ->update(['status' => 'canceled']);
                                                }
                                            
                                                \Log::info("Reservation inserted successfully for message: $messageId");
                                            }
            
                                       
                                    }
                            } catch (\Exception $e) {
                                \Log::error("Error processing attachment: " . $e->getMessage());
                                continue;
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::error("Error processing message: " . $e->getMessage());
                        continue;
                    }
                }
        
                echo 'end - success ' . $time->toDateTimeString();
        
            } catch (\Exception $e) {
                \Log::error("General error in getemails_new: " . $e->getMessage());
                echo 'Une erreur est survenue lors de la récupération des emails.';
            }
        }

    
    
    
    
    private function mapJsonToReservation(array $json,$messageId,$emailSubject ): array
        {
            $reservation = [
                // 'ref_reservation'     => $json['id'] ?? null,
                'ref_reservation'     => 'CIVI-'.$json['id'] ?? null,
                'title'               => $json['articulo'] ?? $json['trayecto'] ?? null,
                'client_nom'          => trim(($json['nombre'] ?? '') . ' ' . ($json['apellidos'] ?? '')),
                'client_email'        => $json['email'] ?? null,
                'client_telephone'    => $json['telefono'] ?? null,
                'date_resa'           => $json['fechaRecogida'] ??  $json['fechaRealizacion'] ?? null,
                'hour_start'          => $json['horaRecogida'] ?? null,
                'pax_adulte'          => $json['numPersonas'] ?? $json['numViajeros'] ?? null,
                'vol_heur'            => $json['horaRecogida'] ?? $json['horaRealizacion'] ?? null,
                'vol_date'            => $json['fechaRecogida'] ?? $json['fechaRealizacion'] ?? null,
                'subject'             => $emailSubject,
                'prixtotal'           =>0,
                'devise_id'           => 1,
                'pick_up'             => $json['puntoRecogida'] ?? null,
                'typeresa'            => $json['tipo'] ?? null,
                'gyginfos'            => null,
                'message_id'          => $messageId,
                'hotel_adresse'       => $json['puntoRecogida'] ?? $json['hotelPuntoDestino'] ?? null,
                'status'              => 'gmail'
            ];
        
            $extra = [];
            $extraKeys = [
                'aerolineaNumeroVuelo',
                'modalidad',
                'tickets',
                'preguntasAdicionales',
                'fechaReservaGMT',
                'comentarios'
            ];
            
            $values = [];
            
            foreach ($extraKeys as $key) {
                if (isset($json[$key])) {
                    if (is_array($json[$key])) {
                        
                        foreach ($json[$key] as $item) {
                            $values[] = is_array($item) ? implode(', ', $item) : $item;
                        }
                    } else {
                        $values[] = $json[$key];
                    }
                }
            }
            
            if (!empty($values)) {
            
                $reservation['gyginfos'] = implode(' | ', $values);
            }
        
            return $reservation;
        }

    
    
    public function refreshGmailToken()
    {
        // Check if token is already valid
        /*if (LaravelGmail::check()) {
            return true; // Token is valid, no refresh needed
        }*/
    
        try {
            // Attempt to refresh the token
            LaravelGmail::makeToken(); 
            
            // Confirm if token has been refreshed
            if (LaravelGmail::check()) {
                return true; // Successfully refreshed
            } else {
                throw new \Exception('Token refresh failed. Token is still invalid.');
            }
    
        } catch (\Exception $e) {
            Log::error('Failed to refresh Gmail token: ' . $e->getMessage());
    
            // Redirect to re-authenticate only if refresh token is missing or invalid
            return redirect('/oauth/gmail');
        }
    }



}
