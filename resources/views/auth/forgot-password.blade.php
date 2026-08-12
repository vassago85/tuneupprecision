<x-layouts.site title="Forgot password">
  <section class="auth-wrap">
    <div class="wrap">
      <div class="auth-card reveal">
        <div class="sec-head" style="margin-bottom:22px">
          <span class="eyebrow">Password reset</span>
          <h2 style="font-size:36px">Forgot your password?</h2>
          <p>Drop your email address in and we'll send a link to set a new one.</p>
        </div>

        @if (session('status'))
          <div class="auth-note ok">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="auth-form" novalidate>
          @csrf

          <div class="form-field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
            @error('email')<div class="form-err">{{ $message }}</div>@enderror
          </div>

          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Email reset link</button>

          <p class="auth-alt">Remembered it? <a href="{{ route('login') }}">Back to sign in</a>.</p>
        </form>
      </div>
    </div>
  </section>
</x-layouts.site>
