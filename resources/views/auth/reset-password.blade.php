<x-layouts.site title="Reset password">
  <section class="auth-wrap">
    <div class="wrap">
      <div class="auth-card reveal">
        <div class="sec-head" style="margin-bottom:22px">
          <span class="eyebrow">Password reset</span>
          <h2 style="font-size:36px">Set a new password.</h2>
          <p>Pick a new password for your Tune Up account.</p>
        </div>

        <form method="POST" action="{{ route('password.update') }}" class="auth-form" novalidate>
          @csrf
          <input type="hidden" name="token" value="{{ $token }}">

          <div class="form-field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $email) }}" autocomplete="email" required autofocus>
            @error('email')<div class="form-err">{{ $message }}</div>@enderror
          </div>

          <div class="form-field">
            <label for="password">New password</label>
            <input id="password" type="password" name="password" autocomplete="new-password" required minlength="8">
            <div class="form-hint">At least 8 characters.</div>
            @error('password')<div class="form-err">{{ $message }}</div>@enderror
          </div>

          <div class="form-field">
            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required minlength="8">
          </div>

          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Reset password</button>
        </form>
      </div>
    </div>
  </section>
</x-layouts.site>
