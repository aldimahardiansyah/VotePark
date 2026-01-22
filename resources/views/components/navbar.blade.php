@php
    $currentRoute = Route::currentRouteName();
@endphp
<nav class="navbar navbar-expand-lg bg-body-tertiary">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('home') }}">Proapps Event</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                @auth
                    @if (auth()->user()->role !== 'user')
                        <li class="nav-item">
                            <a class="nav-link {{ $currentRoute == 'unit.index' ? 'active' : '' }}" aria-current="page" href="{{ route('unit.index') }}">Master Data</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $currentRoute == 'event.index' ? 'active' : '' }}" href="{{ route('event.index') }}">Events</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ str_starts_with($currentRoute, 'anonymous-voting.') ? 'active' : '' }}" href="{{ route('anonymous-voting.index') }}">Anonymous Voting</a>
                        </li>
                        @if (auth()->user()->isHoldingAdmin())
                            <li class="nav-item">
                                <a class="nav-link {{ str_starts_with($currentRoute, 'site.') ? 'active' : '' }}" href="{{ route('site.index') }}">Sites</a>
                            </li>
                        @endif
                    @endif
                @endauth
            </ul>
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                @guest
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}">Register</a>
                    </li>
                @else
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ auth()->user()->name }}
                            @if (auth()->user()->role !== 'user')
                                <span class="badge bg-primary">{{ str_replace('_', ' ', ucwords(auth()->user()->role, '_')) }}</span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('home') }}">Home</a>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    Logout
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </li>
                        </ul>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
