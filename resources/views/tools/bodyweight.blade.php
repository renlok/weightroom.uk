@extends('layouts.master')

@section('title', 'Bodyweight')

@include('common.graph', ['type' => 'Bodyweight', 'message' => 'View how your bodyweight has changed.'])
