<x-layouts.site title="Create account">
  <section class="auth-wrap">
    <div class="wrap">
      <div class="auth-card reveal">
        <div class="sec-head" style="margin-bottom:22px">
          <span class="eyebrow">New member</span>
          <h2 style="font-size:36px">Create an account.</h2>
          <p>Sign up to save your details and access the members-only videos once Dirk verifies you.</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="auth-form" novalidate>
          @csrf

          <div class="form-field">
            <label for="name">Full name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" autocomplete="name" required autofocus>
            @error('name')<div class="form-err">{{ $message }}</div>@enderror
          </div>

          <div class="form-field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
            @error('email')<div class="form-err">{{ $message }}</div>@enderror
          </div>

          <div class="form-field">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" autocomplete="new-password" required minlength="8">
            <div class="form-hint">At least 8 characters.</div>
            @error('password')<div class="form-err">{{ $message }}</div>@enderror
          </div>

          <div class="form-field">
            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required minlength="8">
          </div>

          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Create account</button>

          <p class="auth-alt">Already have one? <a href="{{ route('login') }}">Sign in</a>.</p>
        </form>
      </div>
    </div>
  </section>
</x-layouts.site>
