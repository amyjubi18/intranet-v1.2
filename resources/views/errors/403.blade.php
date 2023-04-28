@extends('errors::minimal')

@section('title', __('Acceso Invalido '))
@section('code', '403')
@section('message', __($exception->getMessage() ?: 'No tiene acceso a esta página'))
