@extends('layouts.master')

@section('title', 'Contact Us')

@section('headerstyle')
@endsection

@section('content')
  <div class="narrow-centre-block">
  <h2>Contact Us</h2>
  @include('errors.validation')
  @include('common.flash')

  <form action="{{ route('contactUs') }}" method="post">
    <div class="form-group">
      <label for="name">Your name</label>
      <input type="text" class="form-control" id="name" placeholder="Your name">
    </div>
    <div class="form-group">
      <label for="email">Email address</label>
      <input type="email" class="form-control" id="email" placeholder="Email">
    </div>
    <div class="form-group">
      <label for="message">Your Message</label>
      <textarea class="form-control" id="message"></textarea>
    </div>
    <div class="form-group">
      {!! csrf_field() !!}
      <button type="submit" class="btn btn-primary">Contact Us</button>
    </div>
  </form>
</div>

@endsection

@section('endjs')
@endsection
