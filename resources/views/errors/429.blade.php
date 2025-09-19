@extends('errors.base')

@section('title', 'Too Many Requests')
@section('code', '429')
@section('message', 'You have made too many requests.')
@section('details', 'Please wait a few minutes before trying again.')
