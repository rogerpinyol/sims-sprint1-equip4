<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<div class="container">
    <div class="mb-6">
        <h1 class="text-4xl font-bold text-orange-700 text-center my-8 tracking-wide">Gestió de Vehicles</h1>
    </div>

    <?php if (empty($vehicles)): ?>
        <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-lg">
            <p class="text-yellow-800">No hi ha vehicles registrats. <a href="/vehicle/create" class="underline">Afegeix el primer!</a></p>
        </div>
    <?php else: ?>
        <div class="bg-white shadow-xl rounded-lg overflow-hidden">
            <table class="min-w-full table">
                <thead>
                    <tr>
                        <th class="py-4 px-6 text-center">ID</th>
                        <th class="py-4 px-6 text-center">VIN</th>
                        <th class="py-4 px-6 text-center">Model</th>
                        <th class="py-4 px-6 text-center">Bateria</th>
                        <th class="py-4 px-6 text-center">Estat</th>
                        <th class="py-4 px-6 text-center">Accions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($vehicles as $v): ?>
                    <tr class="border-t">
                        <td class="py-4 px-6 font-mono text-center">#<?= htmlspecialchars($v['id']) ?></td>
                        <td class="py-4 px-6 font-mono text-sm text-center"><?= htmlspecialchars($v['vin'] ?? '') ?></td>
                        <td class="py-4 px-6 font-semibold text-center"><?= htmlspecialchars($v['model'] ?? '') ?></td>
                        <td class="py-4 px-6 text-center">
                            <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 rounded-full font-bold">
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
                            <a href="/vehicle/edit?id=<?= htmlspecialchars($v['id']) ?>" class="btn btn-blue text-xs">Editar</a>
                            <a href="/vehicle/delete?id=<?= htmlspecialchars($v['id']) ?>" class="btn btn-red text-xs" onclick="return confirm('Segur?')">Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>