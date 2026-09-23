<x-layouts.site
    title="Leave a testimonial"
    description="Tell Tune Up Precision what you thought of your training day — your words help other shooters know what to expect."
>
  <section class="auth-wrap">
    <div class="wrap">
      <div class="auth-card reveal">
        <div class="sec-head" style="margin-bottom:22px">
          <span class="eyebrow">Testimonial</span>
          <h2 style="font-size:36px">Tell us how it went.</h2>
          <p>A short review from a shooter is the most useful thing we can share with the next intake. We'll only publish once Dirk gives it a quick read.</p>
        </div>

        @if ($trainingType || $trainingEvent)
          <div class="dope" style="margin-bottom:22px">
            <div class="dope-bd">
              @if ($trainingType)
                <div class="line">
                  <span class="lb">Discipline</span>
                  <span class="vl">{{ $trainingType->name }}</span>
                </div>
              @endif
              @if ($trainingEvent?->starts_on)
                <div class="line">
                  <span class="lb">Event date</span>
                  <span class="vl">{{ $trainingEvent->starts_on->format('D d M Y') }}</span>
                </div>
              @endif
            </div>
          </div>
        @endif

        <form method="POST" action="{{ route('testimonials.store') }}" class="auth-form" novalidate>
          @csrf
          @if ($trainingType)
            <input type="hidden" name="training_type_id" value="{{ $trainingType->id }}">
          @endif
          @if ($trainingEvent)
            <input type="hidden" name="training_event_id" value="{{ $trainingEvent->id }}">
          @endif

          <div class="form-field">
            <label for="author_name">Name and surname</label>
            <input id="author_name" type="text" name="author_name" value="{{ old('author_name', $authorName) }}" autocomplete="name" required maxlength="120">
            @error('author_name')<div class="form-err">{{ $message }}</div>@enderror
          </div>

          <div class="form-field">
            <label for="author_email">Email</label>
            <input id="author_email" type="email" name="author_email" value="{{ old('author_email', $authorEmail) }}" autocomplete="email" required maxlength="255">
            <div class="form-hint">We'll email you a copy. Your address is not published.</div>
            @error('author_email')<div class="form-err">{{ $message }}</div>@enderror
          </div>

          @unless ($trainingType)
            {{-- Only shown when the link didn't pin a discipline. --}}
            <div class="form-field">
              <label for="training_type_id">Which training did you do?</label>
              <select id="training_type_id" name="training_type_id" required>
                <option value="">Choose a discipline…</option>
                @foreach ($trainingTypeOptions as $type)
                  <option value="{{ $type->id }}" @selected(old('training_type_id') == $type->id)>{{ $type->name }}</option>
                @endforeach
              </select>
              @error('training_type_id')<div class="form-err">{{ $message }}</div>@enderror
            </div>
          @endunless

          <div class="form-field">
            <label for="body">Your testimonial</label>
            <textarea id="body" name="body" rows="6" required minlength="10" maxlength="800" placeholder="A few honest sentences about the day.">{{ old('body') }}</textarea>
            <div class="form-hint">Between 10 and 800 characters.</div>
            @error('body')<div class="form-err">{{ $message }}</div>@enderror
          </div>

          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Submit testimonial</button>
          <p class="form-hint" style="text-align:center">By submitting you agree we may publish your name and testimonial on this site. See our <a class="form-link" href="{{ route('legal.privacy') }}">Privacy Policy</a>.</p>
        </form>
      </div>
    </div>
  </section>
</x-layouts.site>
