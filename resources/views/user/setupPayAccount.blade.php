@extends('layouts.master')

@section('title', 'Setup Payout Account')

@section('content')
    <div class="narrow-centre-block">
        <h2>Setup Payout Account</h2>
        @include('errors.validation')
        @include('common.flash')
        <p>Setup a payment account so you can start charging for your workout plans.</p>
        @if ($customer != null)
            <div class="alert alert-info" role="alert">
            @if (count($customer->verification->fields_needed) > 0)
                There are currently some issue that need resolving before you can continue receiving payments.
                <ul>
            @foreach ($customer->verification->fields_needed as $missing)
                @php
                    switch ($missing) {
                    case 'external_account':
                        echo "<li>You need to <a href=\"". route('setupPayAccountBank') . "\"><strong>attach a card</strong></a> to this account.</li>";
                        break;
                    case 'legal_entity.verification.document':
                        echo "<li>You must submit a valid ID, please send this to verify@weightroom.uk in jpg or png format along with your uesrname.</li>";
                        break;
                    default:
                        echo '<li>' . $missing . '</li>';
                    }
                @endphp
            @endforeach
                </ul>
            @endif
            </div>
        @endif
        <a href="{{ route('setupPayAccountBank') }}" class="btn btn-default margintb">Add/Update bank account</a>
        <form method="post" action="{{ route('setupPayAccount') }}">
            <div class="form-inline">
                <label for="account-type">Account Type:</label>
                <select class="form-control" name="account-type" id="account-type">
                    <option value="individual" {{ ($customer != null && $customer->legal_entity->type == 'individual') ? 'selected="selected' : '' }}>Individual</option>
                    <option value="company" {{ ($customer != null && $customer->legal_entity->type == 'company') ? 'selected="selected' : '' }}>Business</option>
                </select>
            </div>
            <div class="form-inline">
                <label for="first-name">First Name:</label>
                <input class="form-control" name="first-name" id="first-name" value="{{ ($customer != null) ? $customer->legal_entity->first_name : '' }}">
                <label for="last-name">Last Name:</label>
                <input class="form-control" name="last-name" id="last-name" value="{{ ($customer != null) ? $customer->legal_entity->last_name : '' }}">
            </div>
            <label>Date of Birth:</label>
            <div class="form-inline">
                <select class="form-control" name="day" id="day">
                @for ($i = 1; $i <= 31; $i++)
                    <option {{ ($customer != null && $customer->legal_entity->dob->day == $i) ? 'selected="selected' : '' }}>{{ $i }}</option>
                @endfor
                </select>
                <select class="form-control" name="month" id="month">
                @for ($i = 1; $i <= 12; $i++)
                    <option {{ ($customer != null && $customer->legal_entity->dob->month == $i) ? 'selected="selected' : '' }}>{{ $i }}</option>
                @endfor
                </select>
                <select class="form-control" name="year" id="year">
                @for ($year = Carbon::now()->year, $i = $year - 17; $i >= $year - 100; $i--)
                    <option {{ ($customer != null && $customer->legal_entity->dob->year == $i) ? 'selected="selected' : '' }}>{{ $i }}</option>
                @endfor
                </select>
            </div>
            <label for="address-line1">Address:</label>
            <input class="form-control" name="address-line1" id="address-line1" value="{{ ($customer != null) ? $customer->legal_entity->address->line1 : '' }}">
            <label for="address-line1">City:</label>
            <input class="form-control" name="address-city" id="address-city" value="{{ ($customer != null) ? $customer->legal_entity->address->city : '' }}">
            <label for="address-line1">Postcode/Zip:</label>
            <input class="form-control" name="postal-code" id="postal-code" value="{{ ($customer != null) ? $customer->legal_entity->address->postal_code : '' }}">
            <label for="country">Country*</label>
            <select class="form-control" name="country" id="country" {{ ($customer != null) ? 'disabled="true"' : '' }}>
                <option value="AU" {{ ($customer != null && $customer->country == 'AU') ? 'selected="selected' : '' }}>Australia</option>
                <option value="AT" {{ ($customer != null && $customer->country == 'AT') ? 'selected="selected' : '' }}>Austria</option>
                <option value="BE" {{ ($customer != null && $customer->country == 'BE') ? 'selected="selected' : '' }}>Belgium</option>
                <option value="CA" {{ ($customer != null && $customer->country == 'CA') ? 'selected="selected' : '' }}>Canada</option>
                <option value="DK" {{ ($customer != null && $customer->country == 'DK') ? 'selected="selected' : '' }}>Denmark</option>
                <option value="FI" {{ ($customer != null && $customer->country == 'FI') ? 'selected="selected' : '' }}>Finland</option>
                <option value="FR" {{ ($customer != null && $customer->country == 'FR') ? 'selected="selected' : '' }}>France</option>
                <option value="DE" {{ ($customer != null && $customer->country == 'DE') ? 'selected="selected' : '' }}>Germany</option>
                <option value="HK" {{ ($customer != null && $customer->country == 'HK') ? 'selected="selected' : '' }}>Hong Kong</option>
                <option value="IE" {{ ($customer != null && $customer->country == 'IE') ? 'selected="selected' : '' }}>Ireland</option>
                <option value="IT" {{ ($customer != null && $customer->country == 'IT') ? 'selected="selected' : '' }}>Italy</option>
                <option value="JP" {{ ($customer != null && $customer->country == 'JP') ? 'selected="selected' : '' }}>Japan</option>
                <option value="LU" {{ ($customer != null && $customer->country == 'LU') ? 'selected="selected' : '' }}>Luxembourg</option>
                <option value="NE" {{ ($customer != null && $customer->country == 'NE') ? 'selected="selected' : '' }}>Netherlands</option>
                <option value="NZ" {{ ($customer != null && $customer->country == 'NZ') ? 'selected="selected' : '' }}>New Zealand</option>
                <option value="NO" {{ ($customer != null && $customer->country == 'NO') ? 'selected="selected' : '' }}>Norway</option>
                <option value="PT" {{ ($customer != null && $customer->country == 'PT') ? 'selected="selected' : '' }}>Portugal</option>
                <option value="SG" {{ ($customer != null && $customer->country == 'SG') ? 'selected="selected' : '' }}>Singapore</option>
                <option value="ES" {{ ($customer != null && $customer->country == 'ES') ? 'selected="selected' : '' }}>Spain</option>
                <option value="SE" {{ ($customer != null && $customer->country == 'SE') ? 'selected="selected' : '' }}>Sweden</option>
                <option value="CH" {{ ($customer != null && $customer->country == 'CH') ? 'selected="selected' : '' }}>Switzerland</option>
                <option value="GB" {{ ($customer != null && $customer->country == 'GB') ? 'selected="selected' : '' }}>United Kingdom</option>
                <option value="US" {{ ($customer != null && $customer->country == 'US') ? 'selected="selected' : '' }}>United States</option>
            </select>
            <p><small>Only these countries are supported at the moment, if you are from outside those listed you will not be able to accept payments at this time.</small></p>
            <h4>The following is for busniess accounts only</h4>
            <label for="business-name">Business Name:</label>
            <input class="form-control" name="business-name" id="business-name" value="{{ ($customer != null) ? $customer->legal_entity->business_name : '' }}">
            <label for="tax-id">Tax ID:</label>
            <input class="form-control" name="tax-id" id="tax-id" value="{{ ($customer != null) ? $customer->legal_entity->business_tax_id : '' }}">
            {!! csrf_field() !!}
            <p>By registering your account, you comfirm the information you have given is correct and you agree to the <a href="https://stripe.com/connect-account/legal">Stripe Connected Account Agreement</a>.</p>
            <button type="submit" class="btn btn-default">{{ $customer == null ? 'Register' : 'Update' }}</button>
        </form>
    </div>
@endsection

@section('endjs')
@endsection
