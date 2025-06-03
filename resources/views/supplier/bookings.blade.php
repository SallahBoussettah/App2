@extends('layouts.app') {{-- Assuming you have a main layout file --}}

@section('content')
    <h1>Bookings</h1>

    <ul>
        @foreach ($bookings as $booking)
            <li>
                Booking ID: {{ $booking['bookingId'] }}<br>
                Product ID: {{ $booking['productId'] }}<br>
                Status: {{ $booking['status'] }}<br>
                <!-- Add more details as needed -->
            </li>
        @endforeach
    </ul>
@endsection