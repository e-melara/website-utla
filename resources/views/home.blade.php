@extends('layout.app')
@section('title')
    Pagina de asesoria
@endsection

@section('script')
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
    <script src="/js/maestro-asesoria.js"></script>
@show

@section('container')
    <div ng-app='asesoria'>
        <div class="row" ng-controller='AsesoriaController'>

        </div>
    </div>
@endsection
