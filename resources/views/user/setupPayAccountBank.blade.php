@extends('layouts.master')

@section('title', 'Setup Payout Account')

@section('content')
    <div class="narrow-centre-block">
        <h2>Setup Payout Account</h2>
        <small><a href="{{ route('setupPayAccount') }}"><- back</a></small>
        @include('errors.validation')
        @include('common.flash')
        <p>Attach the card to your payment account which you want to receive the payments to.</p>
        <form class="form-horizontal" action="{{ route('setupPayAccountBank') }}" method="post" id="bank-form">
            <div id="payment-errors" class="alert alert-danger" role="alert" style="display:none;"></div>
            <div class="form-group">
                <label for="holder-name" class="control-label">Account Holder Name</label>
                <input type="text" class="form-control" data-stripe="account_holder_name">
            </div>
        @if ($customer->country == 'AU')
            <div class="form-group">
                <label for="bsb" class="control-label">BSB</label>
                <input type="text" class="form-control" size="10" data-stripe="routing_number" maxlength="6">
            </div>
        @elseif ($customer->country == 'BR')
            <div class="form-group">
                <label for="bsb" class="control-label">Bank Code + Branch Code</label>
                <input type="text" class="form-control" size="10" data-stripe="routing_number" maxlength="6">
                <small>Combine the two number to make one long number</small>
            </div>
        @elseif ($customer->country == 'CA')
            <div class="form-group">
                <label for="bsb" class="control-label">Transit Number + Institution Number</label>
                <input type="text" class="form-control" size="10" data-stripe="routing_number" maxlength="6">
                <small>Combine the two number to make one long number</small>
            </div>
        @elseif ($customer->country == 'HK')
            <div class="form-group">
                <label for="bsb" class="control-label">Clearing Code + Branch Code</label>
                <input type="text" class="form-control" size="10" data-stripe="routing_number" maxlength="6">
                <small>Combine the two number to make one long number</small>
            </div>
        @elseif ($customer->country == 'JP' || $customer->country == 'SG')
            <div class="form-group">
                <label for="bsb" class="control-label">Bank Code + Branch Code</label>
                <input type="text" class="form-control" size="10" data-stripe="routing_number" maxlength="6">
                <small>Combine the two number to make one long number</small>
            </div>
        @elseif ($customer->country == 'NZ' || $customer->country == 'US')
            <div class="form-group">
                <label for="bsb" class="control-label">Routing Number</label>
                <input type="text" class="form-control" size="10" data-stripe="routing_number" maxlength="6">
            </div>
        @elseif ($customer->country == 'GB')
            <div class="form-group">
                <label for="bsb" class="control-label">Sort Code</label>
                <input type="text" class="form-control" size="10" data-stripe="routing_number" maxlength="6">
            </div>
        @endif
            <div class="form-group">
                <label for="account-number" class="control-label">{{ (in_array($customer->country, ['AU', 'BR', 'CA', 'HK', 'JP', 'NZ', 'SG', 'GB', 'US'])) ? 'Account Number' : 'IBAN' }}</label>
                <input type="text" class="form-control" size="10" data-stripe="account_number">
            </div>
            <div class="form-group">
                <label for="currency" class="control-label">Card Currency</label>
                <select class="form-control" data-stripe="currency">
                    <option value="AUD">Australian Dollar</option>
                    <option value="CAD">Canadian Dollar</option>
                    <option value="DKK">Danish Krone</option>
                    <option value="EUR">Euro</option>
                    <option value="GBP">Great British Pound</option>
                    <option value="HKD">Hong Kong Dollar</option>
                    <option value="JPY">Japanese Yen</option>
                    <option value="NZD">New Zealand Dollar</option>
                    <option value="NOK">Norwegian Krone</option>
                    <option value="SGD">Singapore Dollar</option>
                    <option value="SEK">Swedish Krona</option>
                    <option value="CHF">Swiss Franc</option>
                    <option value="USD">US Dollar</option>
                </select>
            </div>
            <input type="hidden" data-stripe="country" value="{{ $customer->country }}">
            <input type="hidden" data-stripe="account_holder_type" value="{{ $customer->legal_entity->type }}">
            <div class="form-group">
                {{ csrf_field() }}
                <input type="submit" id="submit-button" class="btn btn-default btn-lg" value="Submit Bank Details">
                <p class="small text-muted">*All of your bank details are securely handled by <a href="https://stripe.com/">stripe</a>, none of your payment information will be saved by us.</p>
            </div>
        </form>
    </div>
@endsection

@section('endjs')
    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
    <script>
        Stripe.setPublishableKey('{{ env('STRIPE_KEY', '') }}');
        $(function() {
            var $form = $('#bank-form');
            $form.submit(function(event) {
                // Disable the submit button to prevent repeated clicks:
                $form.find('#submit-button').prop('disabled', true);
                // Request a token from Stripe:
                Stripe.bankAccount.createToken($form, stripeResponseHandler);
                // Prevent the form from being submitted:
                return false;
            });
        });

        function stripeResponseHandler(status, response) {
            // Grab the form:
            var $form = $('#bank-form');
            if (response.error) { // Problem!
                // Show the errors on the form:
                $form.find('#payment-errors').text(response.error.message);
                $form.find('#payment-errors').css("display", "block");
                $form.find('#submit-button').prop('disabled', false); // Re-enable submission
            } else { // Token was created!
                // Get the token ID:
                var token = response.id;
                // Insert the token ID into the form so it gets submitted to the server:
                $form.append($('<input type="hidden" name="stripeToken">').val(token));alert(token);
                // Submit the form:
                $form.get(0).submit();
            }
        }
    </script>
@endsection
