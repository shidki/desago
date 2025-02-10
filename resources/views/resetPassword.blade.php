<form action="{{ route('password.update') }}" method="POST">
	@csrf
	<input type="email" name="email" id="" placeholder="masukkan email">
<input type="password" name="password" id="" placeholder="masukkan password">
<input type="password" name="password_confirmation" id="" placeholder="konfirmasi password">
<input type="hidden" name="token" id="token" value="{{$token}}">

<button type="submit">submit</button>
</form>