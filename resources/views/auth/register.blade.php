<form method="POST", action="{{route('register.submit')}}">
    @csrf
    <input type="text" name="username" placeholder="Username">
    <input type="email" name="email" placeholder="Name">
    <input type="password" name="password" placeholder="Password">
    <button type="submit">Register</button>

</form>