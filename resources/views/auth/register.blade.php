<form method="POST" action="{{ route('register.submit') }}" enctype="multipart/form-data">
    @csrf
    <input type="text" name="username" placeholder="Username" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    
    <div>
        <label for="bio">Bio</label>
        <textarea id="bio" name="bio"></textarea>
    </div>

    <div>
        <label for="profile_picture">Photo de profil</label>
        <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
    </div>

    <button type="submit">Register</button>
</form>
