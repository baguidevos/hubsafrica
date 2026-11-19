@php
    $customCss = $model->customCss();
    $brand = $model->brandColor();
@endphp
<div class="fb-form fb-form--livewire {{ $model->layout() === 'horizontal' ? 'fb-form--horizontal' : '' }}" id="form-{{ $model->slug }}" data-fb-form="{{ $model->slug }}" @if ($brand) style="--fb-color-primary: {{ $brand }}" @endif>
    @if ($customCss)
        <style>{!! preg_replace('~</style~i', '', $customCss) !!}</style>
    @endif

    @if ($submitted)
        @php
            $postSubmitRedirect = \App\Support\FormSubmissionRedirectResolver::resolveForCurrentRequest(
                auth()->user(),
                $model->sae ?? null,
            );
        @endphp

        <div
            class="fb-success space-y-4"
            role="status"
            @if ($postSubmitRedirect)
                x-data="{ seconds: {{ $postSubmitRedirect['delaySeconds'] }} }"
                x-init="const timer = setInterval(() => { seconds--; if (seconds <= 0) { clearInterval(timer); window.location.href = @js($postSubmitRedirect['url']); } }, 1000)"
            @endif
        >
            <p class="text-sm font-medium">{{ $message }}</p>

            @if ($postSubmitRedirect)
                <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-200/70">
                    <p class="text-xs text-slate-500">
                        Redirection automatique dans <span class="font-bold text-slate-800" x-text="seconds">{{ $postSubmitRedirect['delaySeconds'] }}</span>s...
                    </p>
                    <a
                        href="{{ $postSubmitRedirect['url'] }}"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs"
                    >
                        <span>{{ $postSubmitRedirect['label'] }}</span>
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                </div>
            @endif
        </div>
    @elseif ($error !== null)
        <div class="fb-closed" role="status">{{ $error }}</div>
    @elseif ($locked)
        <form wire:submit="unlock" class="fb-form__unlock">
            <p class="fb-paragraph" style="margin-bottom: 1rem">{{ __('packstub-form-builder::form-builder.frontend.password_prompt') }}</p>
            <x-filament::input.wrapper :valid="$passwordError === null">
                <x-filament::input type="password" wire:model="password" :placeholder="__('packstub-form-builder::form-builder.frontend.password_label')" autocomplete="off" />
            </x-filament::input.wrapper>
            @if ($passwordError)
                <p class="fb-error" style="margin-top: .5rem">{{ $passwordError }}</p>
            @endif
            <div class="fb-actions" style="margin-top: 1rem">
                <x-filament::button type="submit" wire:loading.attr="disabled">
                    {{ __('packstub-form-builder::form-builder.frontend.unlock') }}
                </x-filament::button>
            </div>
        </form>
    @else
        <form
            @if ($captcha)
                x-data
                x-on:submit.prevent="$wire.submit($el.querySelector('[name$=-response]')?.value ?? null)"
            @else
                wire:submit="submit"
            @endif
        >
            {{ $this->form }}

            @if ($captcha)
                <div class="fb-field fb-field--captcha" style="margin-top: 1rem" wire:ignore>
                    {!! $captcha['html'] !!}
                </div>
                @error('captcha')
                    <p class="fb-error" style="margin-top: .5rem">{{ $message }}</p>
                @enderror
                <script src="{{ $captcha['script'] }}" async defer></script>
            @endif

            @unless ($model->isWizard())
                <div class="fb-actions" style="margin-top: 1.5rem">
                    <x-filament::button type="submit" wire:loading.attr="disabled">
                        {{ $model->submitLabel() }}
                    </x-filament::button>
                </div>
            @endunless
        </form>
    @endif

    @if ($model->customJs() && ! $submitted && $error === null && ! $locked)
        <script>(function (form) { {!! preg_replace('~</script~i', '', $model->customJs()) !!} })(document.getElementById(@json('form-'.$model->slug)));</script>
    @endif
</div>
