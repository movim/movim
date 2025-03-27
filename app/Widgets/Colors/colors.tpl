body {
    {loop="$colors"}--p-{$key}: {$value};{/loop}
    --movim-accent: var(--p-{$accentcolor});
}
