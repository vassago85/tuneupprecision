<x-layouts.site title="Unsubscribed">
    <section class="wrap" style="padding:80px 0;min-height:46vh;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center">
        @if ($found)
            <span class="eyebrow">Newsletter</span>
            <h1 style="font-size:44px;margin:14px 0 10px">You're unsubscribed</h1>
            <p style="color:var(--muted);max-width:52ch">
                @if ($email)
                    <strong>{{ $email }}</strong> won't receive any more newsletters from Tune Up Precision.
                @else
                    You won't receive any more newsletters from Tune Up Precision.
                @endif
                Changed your mind? You can re-subscribe from the footer any time.
            </p>
        @else
            <span class="eyebrow">Newsletter</span>
            <h1 style="font-size:44px;margin:14px 0 10px">Link not recognised</h1>
            <p style="color:var(--muted);max-width:52ch">This unsubscribe link is invalid or has already been used. If you keep getting emails, contact <a href="mailto:hello@tuneupprecision.co.za" style="color:var(--copper-deep)">hello@tuneupprecision.co.za</a>.</p>
        @endif
        <a href="{{ url('/') }}" class="btn btn-dark" style="margin-top:26px">Back to home</a>
    </section>
</x-layouts.site>
