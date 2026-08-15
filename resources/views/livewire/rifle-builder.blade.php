<div>
    <header class="nav">
        <div class="wrap nav-in">
            <a class="brand" href="{{ route('home') }}">
                <svg class="mark" viewBox="0 0 40 40" aria-hidden="true"><circle cx="20" cy="20" r="17" fill="none" stroke="var(--copper)" stroke-width="2"/><line x1="20" y1="1" x2="20" y2="14" stroke="#fff" stroke-width="1.6"/><line x1="20" y1="26" x2="20" y2="39" stroke="#fff" stroke-width="1.6"/><line x1="1" y1="20" x2="14" y2="20" stroke="#fff" stroke-width="1.6"/><line x1="26" y1="20" x2="39" y2="20" stroke="#fff" stroke-width="1.6"/><circle cx="20" cy="20" r="2.6" fill="var(--copper)"/></svg>
                <span class="txt">Tune Up<small>Precision Rifle Builder</small></span>
            </a>
            <span class="nav-spacer"></span>
            <span class="quoteref">QUOTE <b>{{ $submittedReference ?: ($shareCode ? strtoupper($shareCode) : '—') }}</b></span>
            <a class="btn ghost" href="{{ route('home') }}" style="width:auto;padding:7px 12px">Site</a>
        </div>
    </header>

    <section class="hero">
        <div class="wrap">
            <svg class="retbg" viewBox="0 0 40 40" aria-hidden="true"><circle cx="20" cy="20" r="17" fill="none" stroke="currentColor" stroke-width="2"/><line x1="20" y1="1" x2="20" y2="14" stroke="currentColor"/><line x1="20" y1="26" x2="20" y2="39" stroke="currentColor"/><line x1="1" y1="20" x2="14" y2="20" stroke="currentColor"/><line x1="26" y1="20" x2="39" y2="20" stroke="currentColor"/></svg>
            <div class="kicker">Custom rifle configurator</div>
            <h1>Build it. <em>Price it.</em> Shoot it.</h1>
            <p>Spec a precision rifle from the Tune Up catalogue. Live totals include VAT. Request the build and Dirk will issue a formal quote.</p>
        </div>
        <div class="strip">
            <div class="wrap strip-in">
                <div>Total <b class="num">{{ \App\Support\Money::format($result->totalCents, false) }}</b></div>
                <div>Components <b class="num">{{ $result->componentCount() }}</b></div>
                <div>Lead time <b>{{ $result->leadTime }}</b></div>
                <div>VAT <b class="num">{{ \App\Support\Money::format($result->vatCents, false) }}</b></div>
            </div>
        </div>
    </section>

    <div class="wrap builder">
        <div>
            <livewire:rifle-build-picker :initial-selection="$buildSelection" :key="$shareCode ?: 'fresh'" />
        </div>
        <aside class="summary">
            <div class="dope">
                <div class="dope-hd">
                    <h3>Build sheet</h3>
                    <span class="rt">DOPE</span>
                </div>
                <div class="dope-bd">
                    @forelse ($result->lines as $line)
                        <div class="line">
                            <span class="lb">{{ $line->group }}</span>
                            <span class="vl">{{ $line->brand }} {{ $line->description }}@if ($line->quantity > 1) ×{{ $line->quantity }}@endif</span>
                            <span class="pv num">{{ $line->lineTotalCents === 0 ? 'INCL' : \App\Support\Money::format($line->lineTotalCents, false) }}</span>
                        </div>
                    @empty
                        <div class="empty">NO COMPONENTS SELECTED</div>
                    @endforelse
                </div>
                <div class="totals">
                    @if ($result->discountCents > 0)
                        <div class="trow"><span>Subtotal</span><span class="num">{{ \App\Support\Money::format($result->subtotalCents, false) }}</span></div>
                        <div class="trow"><span>Discount</span><span class="num">− {{ \App\Support\Money::format($result->discountCents, false) }}</span></div>
                    @endif
                    <div class="trow"><span>Excl. VAT</span><span class="num">{{ \App\Support\Money::format($result->exVatCents, false) }}</span></div>
                    <div class="trow"><span>VAT @ 15%</span><span class="num">{{ \App\Support\Money::format($result->vatCents, false) }}</span></div>
                    <div class="trow grand"><span>Total</span><span class="num">{{ \App\Support\Money::format($result->totalCents, false) }}</span></div>
                </div>
                <div class="acts">
                    <button type="button" class="btn ghost" wire:click="shareBuild">Share</button>
                    <button type="button" class="btn" wire:click="openRequest">Request</button>
                    <button type="button" class="btn wide" wire:click="openRequest">Request this build</button>
                </div>
            </div>
        </aside>
    </div>

    <div class="wrap">
        <div class="divider">
            <span class="ln"></span>
            <svg viewBox="0 0 40 40" aria-hidden="true"><circle cx="20" cy="20" r="17" fill="none" stroke="currentColor" stroke-width="2"/><circle cx="20" cy="20" r="2.6" fill="currentColor"/></svg>
            <span class="ln"></span>
        </div>
    </div>

    <footer class="rb-foot">
        <div class="wrap cols">
            <div>
                <h5>Tune Up Precision</h5>
                <p>Custom rifle builds, chambering and long-range training. Gauteng, by arrangement.</p>
            </div>
            <div>
                <h5>Contact</h5>
                <p class="mono">{{ $business['tel'] }}<br>{{ $business['email'] }}</p>
            </div>
            <div>
                <h5>Dealer</h5>
                <p class="mono">VAT {{ $business['vat_number'] }}<br>Dealer {{ $business['dealer_number'] }}</p>
            </div>
        </div>
    </footer>

    <div class="toast{{ $toast ? ' on' : '' }}" role="status">{{ $toast }}</div>

    <div class="modal{{ $showRequest ? ' on' : '' }}" wire:click.self="$set('showRequest', false)">
        <div class="modal-in">
            <h3>Request this build</h3>
            <p class="sub">We’ll email Dirk the spec and send you a copy. No account needed.</p>
            <div class="fld">
                <label for="req-name">Name</label>
                <input id="req-name" type="text" wire:model="customerName">
                @error('customerName') <div class="fitwarn">{{ $message }}</div> @enderror
            </div>
            <div class="fld">
                <label for="req-email">Email</label>
                <input id="req-email" type="email" wire:model="customerEmail">
                @error('customerEmail') <div class="fitwarn">{{ $message }}</div> @enderror
            </div>
            <div class="fld">
                <label for="req-phone">Phone</label>
                <input id="req-phone" type="text" wire:model="customerPhone">
            </div>
            <div class="fld">
                <label for="req-msg">Message (optional)</label>
                <textarea id="req-msg" rows="3" wire:model="message"></textarea>
            </div>
            <button type="button" class="btn wide" wire:click="submitRequest">Send request</button>
        </div>
    </div>
</div>
