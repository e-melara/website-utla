@extends('layouts.app')

@section('content')
    <main class="d-flex align-items-center min-vh-100 py-3 py-md-0">
        <div class="container">
            <div class="card login-card">
                <div class="row no-gutters">
                    <div class="col-md-5">
                        <img src="/images/login.jpg" alt="login" class="login-card-img">
                    </div>
                    <div class="col-md-7">
                        <div class="card-body">
                            <div class="brand-wrapper">
                                <img src="/images/logo.svg" alt="logo" class="logo">
                            </div>
                            <p class="login-card-description">Logeate para entrar a tu cuenta</p>
                            <form action="{{ route('login.store') }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label for="carnet" class="sr-only">Carnet</label>
                                    <input type="text" name="carnet" id="carnet" class="form-control"
                                        placeholder="Ingrese un carnet valido">
                                </div>
                                <div class="form-group mb-4">
                                    <label for="password" class="sr-only">Password</label>
                                    <input type="password" name="password" id="password" class="form-control"
                                        placeholder="***********">
                                </div>
                                <input name="login" id="login" class="btn btn-block login-btn mb-4" type="submit"
                                    value="Login" />
                            </form>

                            <nav class="login-card-footer-nav">
                                <a href="#!">Terms of use.</a>
                                <a href="#!">Privacy policy</a>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
