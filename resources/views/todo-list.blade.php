@extends('layouts.app')

@section('title', 'Todo List')

@section('content')
    @livewire('todo-list', 5)
@endsection