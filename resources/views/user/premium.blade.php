@extends('layouts.master')

@section('title', 'Premium membership')

@section('content')
<div class="narrow-centre-block">
  <h2>Premium Membership</h2>
  @include('common.flash')
  <div class="subscription-box">
    @if (Auth::user()->subscribed('weightroom_gold') && Auth::user()->subscription('weightroom_gold')->onTrial())
    <p>You are currently using a trial of WeightRoom Gold, this will expire at <code>{{ Auth::user()->subscription('weightroom_gold')->trial_ends_at->toDayDateTimeString() }}</code>.</p>
    <p>We hope you are finding WeightRoom premium useful if not we would love if you would let us know why by sending us an <a href="mailto:chris@weightroom.uk">email</a>.</p>
    <a class="btn btn-default" href="{{ route('userCancelPremium') }}" role="button">Cancel Premium</a>
    @elseif (Auth::user()->subscribed('weightroom_gold') && Auth::user()->subscription('weightroom_gold')->onGracePeriod())
    <p>We are sorry you don't want your premium account any more. If you change your mind it is as easy as one click away.</p>
    <p>Your WeightRoom Gold membership will expire at <code>{{ Auth::user()->subscription('weightroom_gold')->ends_at->toDayDateTimeString() }}</code>.</p>
    <a class="btn btn-default" href="{{ route('userResumePremium') }}" role="button">Resume Premium</a>
    @elseif (Auth::user()->subscribed('weightroom_gold'))
    <p>We would like to give you a huge thank you for supporting us.</p>
    <p>We hope you are finding WeightRoom Gold useful if not we would love if you would let us know why by sending us an <a href="mailto:chris@weightroom.uk">email</a>.</p>
    <a class="btn btn-default" href="{{ route('userCancelPremium') }}" role="button">Cancel Premium</a>
    @else
    <p>Currently, during the beta stages of WeightRoom all features are free for everyone, but if you like what we are doing and would like to support us we would definitely appreciate it.</p>
    <p>Why not support us for just $5 a month and of course if you just want to support us with a one off payment you can easily cancel the subscription at any time.</p>
    @include('common.stripePayment', ['paymentRoute' => route('userPremium'), 'subscription' => true])
    @endif
  </div>
</div>
@endsection

@section('endjs')
@endsection
