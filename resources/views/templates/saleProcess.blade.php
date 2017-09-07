@extends('layouts.master')

@section('title', 'Purchase Template: ' . $template->template_name)

@section('headerstyle')

@endsection

@section('content')
    <h2>{{ $template->template_name }}</h2>
    <p class="small"><a href="{{ route('templatesHome') }}">← Back to templates</a></p>
    @if ($template->template_description != '')
        <p>{{ $template->template_description }}</p>
    @endif

    @include('common.flash')

    <h3>${{ $template->template_charge }}</h3>

    @include('common.stripePayment', ['paymentRoute' => route('templateSaleProcess', ['template_id' => $template->template_id]), 'subscription' => false])
@endsection
