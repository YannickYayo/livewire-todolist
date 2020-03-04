@extends('layouts.app')

@section('title', 'Todo List')

@section('content')
<livewire:todo-list :pagination="5" />
@endsection