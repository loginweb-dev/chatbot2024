@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
			<h4>QR bot #1</h4>
     		<img src="{{ asset('bot/qrwb1.png')}}">
        </div>
        <div class="col-md-6">
			<h4>QR bot #2</h4>
     		<img src="{{ asset('bot/qrwb2.png')}}">
        </div>
   		<div class="col-md-6">
			<h4>QR bot #3</h4>
     		<img src="{{ asset('bot/qrwb3.png')}}">
        </div>
   		<div class="col-md-6">
			<h4>QR bot #4</h4>
     		<img src="{{ asset('bot/qrwb4.png')}}">
        </div>
    </div>
</div>
@endsection
