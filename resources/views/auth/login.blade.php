<form method="POST", action="{{route('api.login')}}">
    @csrf
    <input type="email" name="email" placeholder="Name">
    <input type="password" name="password" placeholder="Password">
    <button type="submit">Login</button>

</form>