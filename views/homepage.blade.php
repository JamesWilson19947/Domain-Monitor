@extends('layouts.app')

@section('title', 'Page Title')

@section('content')
    <table class="table">
  <thead>
    <tr>
      <th scope="col">Domain Name</th>
      <th scope="col">SSL Expiry</th>
      <th scope="col">Domain Expiry</th>
    </tr>
  </thead>
  <tbody>
  	@foreach($data as $domain)
	    <tr>
	      <td><a href="{{ $domain['domain'] }}">{{ $domain['domain'] }}</a></td>
	      <td>{{ $domain['SSLExpiry'] }}</td>
	      <td>@isset($domain['DomainExpiry']){{ $domain['DomainExpiry'] }}@endisset</td>
	    </tr>
    @endforeach
  </tbody>
</table>
@endsection


	




