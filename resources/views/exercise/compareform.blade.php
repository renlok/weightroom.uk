@extends('layouts.master')

@section('title', 'Compare Exercises')

@section('headerstyle')
@endsection

@section('content')
@include('errors.validation')
@include('exercise.common.compareform')
@endsection

@section('endjs')
@endsection
