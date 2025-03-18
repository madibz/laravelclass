<form method="POST", action="{{route('login.submit')}}">
    @csrf
    <input type="email" name="email" placeholder="Name">
    <input type="password" name="password" placeholder="Password">
    <button type="submit">Login</button>

</form>