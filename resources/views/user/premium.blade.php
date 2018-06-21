@extends('layouts.master')

@section('title', 'Premium membership')

@section('content')
<div class="narrow-centre-block">
  <h2>Premium Membership</h2>
  @include('common.flash')
  <div class="subscription-box">
  @if (Auth::user()->subscribed('weightroom_gold') && Auth::user()->subscription('weightroom_gold')->onTrial())
    <p class="lead">You are currently using a trial of WeightRoom Gold, this will expire at <code>{{ Auth::user()->subscription('weightroom_gold')->trial_ends_at->toDayDateTimeString() }}</code>.</p>
    <p class="lead">We hope you are finding WeightRoom useful and we would love to hear from you. Why not send us an <a href="mailto:chris@weightroom.uk">email</a>.</p>
    <a class="btn btn-default" href="{{ route('userCancelPremium') }}" role="button">Cancel Premium</a>
  @elseif (Auth::user()->subscribed('weightroom_gold') && Auth::user()->subscription('weightroom_gold')->onGracePeriod())
    <p class="lead">Thank you so much for supporting us while you did. We are sad that you now longer can or don't wont to but if you change your mind it is as easy as one click away.</p>
    <p class="lead">Your WeightRoom Gold membership will expire at <code>{{ Auth::user()->subscription('weightroom_gold')->ends_at->toDayDateTimeString() }}</code>.</p>
    <a class="btn btn-default" href="{{ route('userResumePremium') }}" role="button">Resume Premium</a>
  @elseif (Auth::user()->subscribed('weightroom_gold'))
    <p class="lead">We would like to give you a huge thank you for supporting us.</p>
    <p class="lead">We hope you are finding WeightRoom useful and we would love to hear from you. Why not send us an <a href="mailto:chris@weightroom.uk">email</a>.</p>
    <a class="btn btn-default" href="{{ route('userCancelPremium') }}" role="button">Cancel Premium</a>
  @else
    <p class="lead">Why not support us for just $5 a month and of course if you just want to support us with a one off payment you can easily cancel the subscription at any time.</p>
    <p class="lead">By supporting us you are helping keep the site running, making sure we have time to dedicate to adding new features.</p>
    <h3 class="strong">What are the benefits</h3>
    <p class="lead">Besides showing that you love us, you will gain access to export log and private logs.</p>
    @include('common.stripePayment', ['paymentRoute' => route('userPremium'), 'subscription' => true])
  @endif
  </div>
</div>
@endsection

@section('endjs')
@endsection
