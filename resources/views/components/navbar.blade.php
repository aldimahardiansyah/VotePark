@php
    $currentRoute = Route::currentRouteName();
@endphp
<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Votes</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ $currentRoute == 'unit.index' ? 'active' : '' }}" aria-current="page" href="{{ route('unit.index') }}">Unit</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $currentRoute == 'event.index' ? 'active' : '' }}" href="{{ route('event.index') }}">Events</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
