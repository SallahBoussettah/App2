@extends('layouts.app') {{-- Assuming you have a main layout file --}}

@section('content')
    <h1>Reservations</h1>

    <ul>
        @foreach ($reservations as $reservation)
            <li>
                Reservation ID: {{ $reservation['reservationId'] }}<br>
                Product ID: {{ $reservation['productId'] }}<br>
                Status: {{ $reservation['status'] }}<br>
                <!-- Add more details as needed -->
            </li>
        @endforeach
    </ul>
@endsection