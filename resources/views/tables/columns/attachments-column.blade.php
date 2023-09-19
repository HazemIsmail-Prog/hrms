<div>
    @if ($getState())
    <div class=" flex items-center gap-2">
        @foreach ($getState() as $attachment)
            <a class="border px-3 py-0.5 rounded-lg" target="__blank" href="{{ asset('storage/' . $attachment->file) }}">
                <div>{{ $attachment->notes }}</div>
                @if ($attachment->expiration_date)
                <div class=" text-xs">{{ $attachment->expiration_date->format('d-m-Y') }}</div>
                @endif
            </a>
            @endforeach
        </div>
    @endif
</div>
