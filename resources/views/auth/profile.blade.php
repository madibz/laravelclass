<div class="container">
    <h1>Profil de {{ $user->username }}</h1>
 
    <p><strong>Email :</strong> {{ $user->email }}</p>
    <p><strong>Bio :</strong> {{ $user->bio ?? 'Aucune bio' }}</p>
 
    @if ($user->profile_picture)
        <p><strong>Photo de profil :</strong></p>
        <img src="{{ asset('storage/' . $user->profile_picture) }}" alt="Profile Picture" width="150">
    @else
        <p>Aucune photo de profil</p>
    @endif
 
    <a href="{{ route('logout') }}">Se déconnecter</a>
</div>