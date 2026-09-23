<x-filament-widgets::widget>
    <x-filament::section
        icon="heroicon-o-link"
        icon-color="primary"
        heading="Shooter testimonial link"
        description="Send this to a shooter. They write the testimonial and submit it. It stays hidden until you approve it."
    >
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center"
            x-data="{
                copied: false,
                url: @js($url),
                async copy() {
                    try {
                        await navigator.clipboard.writeText(this.url);
                    } catch (e) {
                        this.$refs.link.focus();
                        this.$refs.link.select();
                        document.execCommand('copy');
                    }
                    this.copied = true;
                    setTimeout(() => { this.copied = false }, 2000);
                },
            }"
        >
            <input
                x-ref="link"
                type="text"
                readonly
                x-bind:value="url"
                aria-label="Testimonial link for shooters"
                class="fi-input block w-full min-w-0 flex-1"
                style="padding:0.5rem 0.75rem"
            />

            <div class="flex shrink-0 gap-2">
                <x-filament::button
                    color="gray"
                    icon="heroicon-o-arrow-top-right-on-square"
                    tag="a"
                    :href="$url"
                    target="_blank"
                >
                    Open
                </x-filament::button>

                <x-filament::button
                    icon="heroicon-o-clipboard-document"
                    :loading-indicator="false"
                    x-on:click="copy()"
                >
                    <span x-show="!copied">Copy link</span>
                    <span x-show="copied" x-cloak>Copied</span>
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
