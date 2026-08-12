{{-- Sticky top nav, extracted from the approved mockup. --}}
<header class="nav">
  <div class="wrap nav-inner">
    <a class="brand" href="{{ url('/') }}" aria-label="Tune Up home">
      <svg class="mark" viewBox="0 0 40 40" aria-hidden="true">
        <circle cx="20" cy="20" r="18" fill="none" stroke="#2C3E50" stroke-width="2"/>
        <line x1="20" y1="2" x2="20" y2="14" stroke="#2C3E50" stroke-width="2"/>
        <line x1="20" y1="26" x2="20" y2="38" stroke="#2C3E50" stroke-width="2"/>
        <line x1="2" y1="20" x2="14" y2="20" stroke="#2C3E50" stroke-width="2"/>
        <line x1="26" y1="20" x2="38" y2="20" stroke="#2C3E50" stroke-width="2"/>
        <circle cx="20" cy="20" r="3.4" fill="#D45B2E"/>
      </svg>
      <span class="wm"><b>TUNE UP</b><span>LONG RANGE PRECISION</span></span>
    </a>
    <nav class="links">
      <a href="{{ route('courses') }}">Courses</a>
      <a href="{{ route('shop') }}">Shop</a>
      <a href="{{ route('range') }}">The Range</a>
      <a href="{{ route('calendar') }}">Calendar</a>
      <a href="{{ url('/#about') }}">About</a>
    </nav>
    <div class="nav-actions">
      <button class="cart-btn" id="cartBtn" aria-label="View cart">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M2 3h3l2.2 12.2a1.5 1.5 0 0 0 1.5 1.3h8.4a1.5 1.5 0 0 0 1.5-1.2L21 7H6"/></svg>
        <span class="cart-badge" id="cartBadge">0</span>
      </button>
      @auth
        <form method="POST" action="{{ route('logout') }}" class="nav-user">
          @csrf
          <span class="nav-user-name">Hi, {{ auth()->user()->firstName() }}</span>
          <button type="submit" class="nav-user-out">Sign out</button>
        </form>
      @else
        <a href="{{ route('login') }}" class="nav-user-in">Sign in</a>
      @endauth
      <button class="hamburger" id="hamburger" aria-label="Open menu" aria-expanded="false">
        <svg viewBox="0 0 24 24"><line x1="4" y1="8" x2="20" y2="8"/><line x1="4" y1="16" x2="20" y2="16"/></svg>
      </button>
    </div>
  </div>
</header>
<div class="mobile-menu" id="mobileMenu">
  <a href="{{ route('courses') }}">Courses</a>
  <a href="{{ route('shop') }}">Shop</a>
  <a href="{{ route('range') }}">The Range</a>
  <a href="{{ route('calendar') }}">Calendar</a>
  <a href="{{ url('/#about') }}">About</a>
  @auth
    <form method="POST" action="{{ route('logout') }}" class="mobile-signout">
      @csrf
      <span class="mobile-hello">Signed in as {{ auth()->user()->firstName() }}</span>
      <button type="submit" class="btn btn-ghost" style="width:100%;justify-content:center">Sign out</button>
    </form>
  @else
    <a href="{{ route('login') }}">Sign in</a>
    <a href="{{ route('register') }}" class="btn btn-primary" style="justify-content:center">Create account</a>
  @endauth
</div>
