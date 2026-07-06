@if (isset($keywordMatch) && is_numeric($keywordMatch))
    <span @class([
        'badge badge-outline',
        $compact ?? false ? 'badge-secondary' : 'badge-primary',
    ])>
        @if ($compact ?? false)
            {{ (int) round($keywordMatch) }}%
        @else
            Keyword match {{ (int) round($keywordMatch) }}%
        @endif
    </span>
@endif
