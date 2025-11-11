<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<div class="container">
    <div class="mb-6">
        <h1 class="text-4xl font-bold text-brand text-center my-8 tracking-wide">Gestió de Vehicles</h1>
    </div>

    <?php if (empty($vehicles)): ?>
        <div class="bg-surface-subtle border border-surface p-4 rounded-lg">
            <p class="text-neutral-900">No hi ha vehicles registrats. <a href="/vehicle/create" class="underline text-brand">Afegeix el primer!</a></p>
        </div>
    <?php else: ?>
        <div class="bg-white shadow-xl rounded-lg overflow-hidden">
            <table class="min-w-full table">
                <thead>
                    <tr>
                        <th class="py-4 px-6 text-center" style="text-align:center">VIN</th>
                        <th class="py-4 px-6 text-center" style="text-align:center">Model</th>
                        <th class="py-4 px-6 text-center" style="text-align:center">Bateria</th>
                        <th class="py-4 px-6 text-center" style="text-align:center">Estat</th>
                        <th class="py-4 px-6 text-center" style="text-align:center">Accions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehicles as $v): ?>
                    <tr class="border-t">
                        <td class="py-4 px-6 font-mono text-sm text-center"><?= htmlspecialchars($v['vin'] ?? '') ?></td>
                        <td class="py-4 px-6 font-semibold text-center"><?= htmlspecialchars($v['model'] ?? '') ?></td>
                        <td class="py-4 px-6 text-center">
                            <span class="inline-block px-3 py-1 bg-surface-subtle text-neutral-900 rounded-full font-bold">
                                <?= htmlspecialchars($v['battery_capacity'] ?? '0') ?> kWh
                            </span>
                        </td>
                        <td class="py-4 px-6 text-center">
                            <?php 
                            $status = $v['status'] ?? 'offline';
                            $badge = "badge-$status";
                            ?>
                            <span class="badge <?= $badge ?>"><?= ucfirst($status) ?></span>
                        </td>
                        <td class="py-4 px-6 text-center space-x-2">
                            <a href="/vehicle/edit?id=<?= htmlspecialchars($v['id']) ?>" class="inline-block px-3 py-1 bg-brand text-white rounded text-xs">Editar</a>
                            <button type="button" data-href="/vehicle/delete?id=<?= htmlspecialchars($v['id']) ?>" data-name="<?= htmlspecialchars($v['model'] ?? $v['vin'] ?? 'vehicle') ?>" class="px-3 py-1 bg-red-600 text-white rounded text-xs js-delete">Eliminar</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>