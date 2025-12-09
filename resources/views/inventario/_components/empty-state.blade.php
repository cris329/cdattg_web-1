@props([
    'title' => 'No hay elementos para mostrar',
    'description' => 'No existen registros que coincidan con los criterios actuales.',
    'icon' => 'fas fa-box-open',
    'iconSize' => '4rem',
    'iconColor' => 'text-muted',
    'titleColor' => 'text-muted',
    'descriptionColor' => 'text-muted'
])

<div class="empty-state text-center py-5">
    <i class="{{ $icon }} {{ $iconColor }} mb-3" style="font-size: {{ $iconSize }};"></i>
    <h5 class="{{ $titleColor }}">{{ $title }}</h5>
    <p class="{{ $descriptionColor }} mb-0">{{ $description }}</p>
</div>

