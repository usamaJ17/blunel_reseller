@extends('errors.master')
@section('title')
    {{ __('Error').' '.__('404') }}
@endsection
@section('content')
    <section class="section">
      <div class="container mt-5">
        <div class="page-error">
          <div class="page-inner">
            <h1>{{__('404')}}</h1>
            <div class="page-description">
              {{__('The page you were looking for could not be found')}}
            </div>
            <div class="page-search">
              <div class="mt-3">
                <a href="{{ url()->previous() }}" class="btn btn-primary btn-lg">{{__('Back')}}</a>
              </div>
            </div>
          </div>
        </div>
        <div class="simple-footer mt-5">
            {{ settingHelper('copyright', App::getLocale()) }}
        </div>
      </div>
    </section>
@endsection
