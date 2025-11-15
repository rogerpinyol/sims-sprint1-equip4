<nav class="bg-neutral-900 p-4 text-white">
    <div class="container mx-auto flex items-center justify-between">
        <a href="/" class="text-xl font-bold normal-case">EcoMotion</a>
        <div class="flex items-center gap-4">
            <a href="/vehicle/create" class="inline-block px-3 py-1 border border-brand text-brand rounded hover:bg-brand hover:text-white transition">
                Afegir Vehicle
            </a>
            <a href="/vehicles" class="px-3 hover:text-brand">Vehicles</a>
        </div>
    </div>
</nav>
<?php
// Centralized flash alerts displayed under the navbar so all pages show them consistently.
// Accepts these session keys: 'success' (green), 'warning' (yellow), 'error' or 'danger' (red).
$flash = null;
if (!empty($_SESSION['success'])) {
    $flash = ['type' => 'success', 'message' => $_SESSION['success']];
    unset($_SESSION['success']);
} elseif (!empty($_SESSION['warning'])) {
    $flash = ['type' => 'warning', 'message' => $_SESSION['warning']];
    unset($_SESSION['warning']);
} elseif (!empty($_SESSION['error'])) {
    $flash = ['type' => 'danger', 'message' => $_SESSION['error']];
    unset($_SESSION['error']);
} elseif (!empty($_SESSION['danger'])) {
    $flash = ['type' => 'danger', 'message' => $_SESSION['danger']];
    unset($_SESSION['danger']);
}

if ($flash):
    $type = $flash['type'];
    $message = $flash['message'];
    // Tailwind classes per type
    $classes = [
        'success' => 'bg-green-100 border border-green-400 text-green-700',
        'warning' => 'bg-yellow-100 border border-yellow-400 text-yellow-700',
        'danger'  => 'bg-red-100 border border-red-400 text-red-700'
    ];
    $c = $classes[$type] ?? $classes['warning'];
?>
<div class="container mx-auto mt-4 px-4">
    <div class="relative <?= $c ?> px-4 py-3 rounded-lg js-flash" role="alert" aria-live="polite">
        <!-- Close button top-right -->
        <button type="button" class="absolute top-2 right-3 text-current hover:opacity-80 js-flash-close" aria-label="Tancar alerta">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4" aria-hidden="true">
                <path fill-rule="evenodd" d="M10 8.586l4.95-4.95a1 1 0 011.414 1.414L11.414 10l4.95 4.95a1 1 0 01-1.414 1.414L10 11.414l-4.95 4.95a1 1 0 01-1.414-1.414L8.586 10 3.636 5.05A1 1 0 015.05 3.636L10 8.586z" clip-rule="evenodd" />
            </svg>
        </button>
        <div class="pr-8">
            <?= htmlspecialchars($message) ?>
        </div>
    </div>
</div>
<script>
// Dismiss flash alerts when the close button is clicked. Simple fade and remove.
(function(){
    document.querySelectorAll('.js-flash-close').forEach(function(btn){
        btn.addEventListener('click', function(e){
            var flash = this.closest('.js-flash');
            if (!flash) return;
            flash.style.transition = 'opacity 180ms ease, max-height 180ms ease, margin 180ms ease, padding 180ms ease';
            flash.style.opacity = '0';
            flash.style.maxHeight = '0';
            flash.style.margin = '0';
            flash.style.padding = '0';
            setTimeout(function(){
                if (flash && flash.parentNode) flash.parentNode.removeChild(flash);
            }, 220);
        });
    });
})();
</script>
<?php endif; ?>