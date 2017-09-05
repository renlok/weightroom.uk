<form class="form-horizontal" action="{{ $paymentRoute }}" method="post" id="payment-form">
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
        @if ($subscription)
        <p class="small">This will start a recurrent payment of $5 a month</p>
        @endif
        <input type="submit" id="submit-button" class="btn btn-default btn-lg" value="Submit Payment Details">
        <p class="small text-muted">*All of your card details are securely handled by <a href="https://stripe.com/">stripe</a>, none of your payment information will be saved by us.</p>
    </div>
</form>

<script type="text/javascript" src="https://js.stripe.com/v2/"></script>
<script>
    Stripe.setPublishableKey('{{ env('STRIPE_KEY', '') }}');
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
    }
</script>