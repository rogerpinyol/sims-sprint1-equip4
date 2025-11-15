    <footer class="mt-auto bg-neutral-900 text-white p-4 text-center">
        <p>&copy; <?= date('Y') ?> EcoMotion. Tots els drets reservats.</p>
    </footer>

    <!-- Delete confirmation modal (hidden by default) -->
    <div id="delete-modal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
        <div class="absolute inset-0 bg-black opacity-50 z-40"></div>
        <div class="relative bg-white rounded-lg shadow-lg z-50 max-w-lg w-full mx-4">
            <div class="p-6">
                <h2 class="text-xl font-semibold mb-2">Esteu segurs?</h2>
                <p id="delete-modal-msg" class="text-sm text-neutral-900 mb-4">Aquest vehicle s'eliminarà permanentment.</p>
                <div class="flex justify-end gap-3">
                    <button id="delete-cancel" class="px-4 py-2 rounded border border-neutral-900 text-neutral-900">Cancel·la</button>
                    <button id="delete-confirm" class="px-4 py-2 rounded bg-red-600 text-white">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function(){
        function qs(sel, root=document){return root.querySelector(sel)}
        function qsa(sel, root=document){return Array.from(root.querySelectorAll(sel))}

        const modal = qs('#delete-modal');
        const modalMsg = qs('#delete-modal-msg');
        const btnConfirm = qs('#delete-confirm');
        const btnCancel = qs('#delete-cancel');
        let targetHref = null;

        qsa('.js-delete').forEach(btn => {
            btn.addEventListener('click', function(e){
                e.preventDefault();
                targetHref = this.getAttribute('data-href');
                const name = this.getAttribute('data-name') || 'vehicle';
                modalMsg.textContent = `Segur que voleu eliminar "${name}"? Aquesta acció no es pot desfer.`;
                modal.classList.remove('hidden');
            });
        });

        btnCancel && btnCancel.addEventListener('click', function(){ modal.classList.add('hidden'); targetHref = null; });
        btnConfirm && btnConfirm.addEventListener('click', function(){ if (targetHref) window.location.href = targetHref; });

        // close on ESC
        document.addEventListener('keydown', function(e){ if (e.key === 'Escape') modal.classList.add('hidden'); });
    })();
    </script>

</body>
</html>