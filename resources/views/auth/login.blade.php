<x-layouts.site title="Sign in">
  <section class="auth-wrap">
    <div class="wrap">
      <div class="auth-card reveal">
        <div class="sec-head" style="margin-bottom:22px">
          <span class="eyebrow">Members area</span>
          <h2 style="font-size:36px">Sign in.</h2>
          <p>Access your gated videos and course extras.</p>
        </div>

        @if (session('status'))
          <div class="auth-note ok">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="auth-form" novalidate>
          @csrf

          <div class="form-field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
            @error('email')<div class="form-err">{{ $message }}</div>@enderror
          </div>

          <div class="form-field">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" autocomplete="current-password" required>
            @error('password')<div class="form-err">{{ $message }}</div>@enderror
          </div>

          <div class="form-row">
            <label class="form-check">
              <input type="checkbox" name="remember" value="1">
              <span>Remember me</span>
            </label>
            <a class="form-link" href="{{ route('password.request') }}">Forgot password?</a>
          </div>

          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Sign in</button>

          <p class="auth-alt">No account yet? <a href="{{ route('register') }}">Create one</a>.</p>
        </form>
      </div>
    </div>
  </section>
</x-layouts.site>
