@extends('layouts.master')

@section('title', 'Premium membership')

@section('content')
<div class="narrow-centre-block">
  <h2>Premium Membership</h2>
  @include('common.flash')
  <p>Currently, during the beta stages of WeightRoom all features will be free for everyone, but if you like what we are doing and would like to support us we would definitely appreciate it.</p>
  <div class="subscription-box">
    @if (Auth::user()->subscription('weightroom_gold')->onTrial())
    <p>You are currently using a trial of WeightRoom Gold, this will expire at <code>{{ Auth::user()->subscription()->trial_ends_at->toDayDateTimeString() }}</code>.</p>
    <p>We hope you are finding WeightRoom premium useful if not we would love if you would let us know why by sending us an <a href="mailto:chris@weightroom.uk">email</a>.</p>
    <a class="btn btn-default" href="{{ route('userCancelPremium') }}" role="button">Cancel Premium</a>
    @elseif (Auth::user()->subscribed('weightroom_gold') && Auth::user()->subscription('weightroom_gold')->onGracePeriod())
    <p>We are sorry you don't want your premium account any more. If you change your mind it is as easy as one click away.</p>
    <p>Your WeightRoom Gold membership will expire at <code>{{ Auth::user()->subscription()->ends_at->toDayDateTimeString() }}</code>.</p>
    <a class="btn btn-default" href="{{ route('userResumePremium') }}" role="button">Resume Premium</a>
    @elseif (Auth::user()->subscribed('weightroom_gold'))
    <p>We hope you are finding WeightRoom premium useful if not we would love if you would let us know why by sending us an <a href="mailto:chris@weightroom.uk">email</a>.</p>
    <a class="btn btn-default" href="{{ route('userCancelPremium') }}" role="button">Cancel Premium</a>
    @else
    <form class="form-horizontal" action="{{ route('userPremium') }}" method="post" id="payment-form">
      <div id="payment-errors" class="alert alert-danger" role="alert" style="display:none;"></div>
      <div class="form-group">
        <label for="number" class="control-label">Card Number</label>
        <input type="text" class="form-control" size="20" data-stripe="number">
      </div>
      <div class="form-group">
        <label for="exp_month" class="control-label">Expiration (MM/YY)</label>
        <div class="form-inline">
          <input type="text" class="form-control" size="2" data-stripe="exp_month"> <b class="lead">/</b>
          <input type="text" class="form-control" size="2" data-stripe="exp_year">
        </div>
      </div>
      <div class="form-group">
        <label for="cvc" class="control-label">CVC</label>
        <input type="text" class="form-control" size="4" data-stripe="cvc">
      </div>
      <div class="form-group">
        <label for="address_zip" class="control-label">Billing Postal Code</label>
        <input type="text" class="form-control" size="6" data-stripe="address_zip">
      </div>
      <div class="form-group">
        {{ csrf_field() }}
        <input type="submit" id="submit-button" class="btn btn-default btn-lg" value="Submit Payment">
      </div>
    </form>
    @endif
  </div>
</div>
@endsection

@section('endjs')
<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script>
    Stripe.setPublishableKey('pk_test_HZw7mzBMuejSpcw5eci28Ndj');
    $(function() {
        var $form = $('#payment-form');
        $form.submit(function(event) {
            // Disable the submit button to prevent repeated clicks:
            $form.find('#submit-button').prop('disabled', true);
            // Request a token from Stripe:
            Stripe.card.createToken($form, stripeResponseHandler);
            // Prevent the form from being submitted:
            return false;
        });
    });

    function stripeResponseHandler(status, response) {
        // Grab the form:
        var $form = $('#payment-form');
        if (response.error) { // Problem!
            // Show the errors on the form:
            $form.find('#payment-errors').text(response.error.message);
            $form.find('#payment-errors').css("display", "block");
            $form.find('#submit-button').prop('disabled', false); // Re-enable submission
        } else { // Token was created!
            // Get the token ID:
            var token = response.id;
            // Insert the token ID into the form so it gets submitted to the server:
            $form.append($('<input type="hidden" name="stripeToken">').val(token));
            // Submit the form:
            $form.get(0).submit();
      }
    };
</script>
@endsection
