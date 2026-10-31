{{--
    Mass actions for a record list (see App\Support\ListActions), in three parts placed by the list:
      part="bar"     what to do with the ticked rows, shown in the toolbar while rows are ticked
      part="menu"    Mass Delete / Mass Update / Mass Email / Export in the "..." menu
      part="dialogs" the dialogs those open
    All three sit inside the list's listPage() scope and use its `selected` and `pageIds`.
--}}
@props(['list', 'part'])

@php
    $mass = \App\Support\ListActions::forUser($list, auth()->user());
    $noun = $mass['noun'] ?? 'record';
    $nouns = \Illuminate\Support\Str::plural($noun);
    $exportUrl = $mass && $mass['export'] ? route('list-actions.export', $list) : null;
@endphp

@if($mass && $mass['pick'])
    @if($part === 'bar')
        <div class="flex items-center gap-3 flex-wrap" x-show="selected.length > 0" x-cloak>
            <span class="font-semibold" x-text="selected.length + (selected.length === 1 ? ' {{ $noun }} selected' : ' {{ $nouns }} selected')"></span>
            <button type="button" class="text-sm underline" style="color: var(--ld-muted)" @click="selected = []">Clear</button>
            @if($mass['fields'])<button type="button" class="ld-outline" @click="$dispatch('list-mass', 'update')">Mass Update</button>@endif
            @if($mass['email'])<button type="button" class="ld-outline" @click="$dispatch('list-mass', 'email')">Mass Email</button>@endif
            @if($exportUrl)<a href="{{ $exportUrl }}" :href="'{{ $exportUrl }}?' + selected.map((id) => 'ids[]=' + id).join('&')" class="ld-outline plain-link">Export</a>@endif
            @if($mass['delete'])<button type="button" class="ld-outline ld-danger" @click="$dispatch('list-mass', 'delete')">Mass Delete</button>@endif
        </div>

    @elseif($part === 'menu')
        @if($mass['delete'])<button type="button" @click="menu = null; $dispatch('list-mass', 'delete')">Mass Delete</button>@endif
        @if($mass['fields'])<button type="button" @click="menu = null; $dispatch('list-mass', 'update')">Mass Update</button>@endif
        @if($mass['email'])<button type="button" @click="menu = null; $dispatch('list-mass', 'email')">Mass Email</button>@endif
        @if($exportUrl)
            <a href="{{ $exportUrl }}" :href="'{{ $exportUrl }}' + (selected.length ? '?' + selected.map((id) => 'ids[]=' + id).join('&') : '')" class="plain-link">
                <span x-text="selected.length ? 'Export {{ $nouns }} ticked (' + selected.length + ')' : 'Export all {{ $nouns }} (CSV)'">Export all {{ $nouns }} (CSV)</span>
            </a>
        @endif
        <hr>

    @elseif($part === 'dialogs')
        <div x-data="listMass(@js($mass['fields']), @js($mass['email'] ? route('list-actions.emails', $list) : null))" @list-mass.window="open($event.detail, selected)"
             class="ld-dialog-wrap" x-show="dialog" x-cloak @keydown.escape.window="dialog = null">
            <div class="ld-dialog-backdrop" @click="dialog = null"></div>
            <div class="ld-dialog" role="dialog" aria-modal="true" aria-labelledby="ld-dialog-title">
                <h2 id="ld-dialog-title">
                    @if($mass['delete'])<span x-show="dialog === 'delete'">Mass Delete</span>@endif
                    @if($mass['fields'])<span x-show="dialog === 'update'">Mass Update</span>@endif
                    @if($mass['email'])<span x-show="dialog === 'email'">Mass Email</span>@endif
                </h2>

                {{-- Nothing ticked yet --}}
                <div x-show="selected.length === 0">
                    <p>Tick the {{ $nouns }} in the list first, then choose this again.</p>
                    <div class="ld-dialog-actions">
                        <button type="button" class="ld-accent" style="border-radius: 4px" x-show="pageIds.length > 0" @click="selected = [...pageIds]">
                            <span x-text="'Tick all ' + pageIds.length + ' on this page'">Tick all on this page</span>
                        </button>
                        <button type="button" class="ld-outline" @click="dialog = null">Close</button>
                    </div>
                </div>

                @if($mass['delete'])
                    <form method="POST" action="{{ route('list-actions.delete', $list) }}" x-show="dialog === 'delete' && selected.length > 0">
                        @csrf
                        <template x-for="id in selected" :key="id"><input type="hidden" name="ids[]" :value="id"></template>
                        <p><strong x-text="selected.length + (selected.length === 1 ? ' {{ $noun }}' : ' {{ $nouns }}')"></strong> will be deleted. Each one is checked like its own Delete button; any that can't be deleted are skipped and listed afterwards.</p>
                        <div class="ld-dialog-actions">
                            <button type="submit" class="ld-accent ld-danger-fill" style="border-radius: 4px">Delete</button>
                            <button type="button" class="ld-outline" @click="dialog = null">Cancel</button>
                        </div>
                    </form>
                @endif

                @if($mass['fields'])
                    <form method="POST" action="{{ route('list-actions.update', $list) }}" x-show="dialog === 'update' && selected.length > 0">
                        @csrf
                        <template x-for="id in selected" :key="id"><input type="hidden" name="ids[]" :value="id"></template>
                        <p>Change <strong x-text="selected.length + (selected.length === 1 ? ' {{ $noun }}' : ' {{ $nouns }}')"></strong> at once.</p>
                        <label for="ld-mass-field" class="ld-sub">Field</label>
                        <select id="ld-mass-field" name="field" class="ld-input" x-model="field" @change="value = ''">
                            @foreach($mass['fields'] as $f)
                                <option value="{{ $f['key'] }}">{{ $f['label'] }}</option>
                            @endforeach
                        </select>
                        <label for="ld-mass-value" class="ld-sub">New value</label>
                        <select id="ld-mass-value" name="value" class="ld-input" x-model="value" required>
                            <option value="">Choose…</option>
                            <template x-for="o in options()" :key="o.value"><option :value="o.value" x-text="o.label"></option></template>
                        </select>
                        <div class="ld-dialog-actions">
                            <button type="submit" class="ld-accent" style="border-radius: 4px" :disabled="!value">Update</button>
                            <button type="button" class="ld-outline" @click="dialog = null">Cancel</button>
                        </div>
                    </form>
                @endif

                @if($mass['email'])
                    <div x-show="dialog === 'email' && selected.length > 0">
                        <p x-show="loading">Finding email addresses…</p>
                        <div x-show="!loading">
                            <p>
                                <strong x-text="emails.length + (emails.length === 1 ? ' address' : ' addresses')"></strong> found<span x-show="missing > 0" x-text="' (' + missing + ' without an email)'"></span>.
                                Your mail app opens with them in BCC, so customers don't see each other.
                            </p>
                            <div class="ld-dialog-actions">
                                <a :href="'mailto:?bcc=' + encodeURIComponent(emails.join(','))" href="mailto:" class="ld-accent plain-link" style="border-radius: 4px" x-show="emails.length > 0" @click="dialog = null">Open in mail app</a>
                                <button type="button" class="ld-outline" x-show="emails.length > 0" @click="navigator.clipboard && navigator.clipboard.writeText(emails.join(', ')); copied = true">
                                    <span x-text="copied ? 'Copied' : 'Copy addresses'">Copy addresses</span>
                                </button>
                                <button type="button" class="ld-outline" @click="dialog = null">Close</button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
@endif
