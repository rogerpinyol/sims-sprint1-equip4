<?php
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php';
?>

<?php if (!isset($vehicle)) { $vehicle = null; } ?>

<div class="container mx-auto p-4 max-w-2xl">
    <h1 class="text-3xl font-bold mb-6 text-orange-600 text-center">Editar Vehicle</h1>

    <form action="/vehicle/update" method="POST" class="bg-white shadow-lg rounded-lg p-4 space-y-4">
        <input type="hidden" name="id" value="<?= htmlspecialchars($vehicle['id'] ?? '') ?>">

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block font-bold mb-1">VIN</label>
                <input type="text" name="vin" value="<?= htmlspecialchars($vehicle['vin'] ?? '') ?>" class="w-full px-3 py-1.5 border rounded-lg">
            </div>
            <div>
                <label class="block font-bold mb-1">Model</label>
                <input type="text" name="model" value="<?= htmlspecialchars($vehicle['model'] ?? '') ?>" class="w-full px-3 py-1.5 border rounded-lg">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block font-bold mb-1">Capacitat Bateria (kWh)</label>
                <input type="number" step="0.01" id="battery_capacity" name="battery_capacity" class="w-full px-3 py-1.5 border rounded-lg" value="<?= htmlspecialchars($vehicle['battery_capacity'] ?? '0') ?>">
            </div>
            <div>
                <label class="block font-bold mb-1">Estat</label>
                <select name="status" class="w-full px-3 py-1.5 border rounded-lg">
                    <?php $statuses = ['available','charging','booked','maintenance','offline'];
                    $current = $vehicle['status'] ?? '';
                    foreach ($statuses as $s): ?>
                        <option value="<?= $s ?>" <?= ($current == $s) ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div>
            <label class="block font-bold mb-1">Ubicació (POINT(lat lon))</label>
            <input type="text" name="location" class="w-full px-3 py-1.5 border rounded-lg" value="<?= htmlspecialchars($vehicle['location'] ?? 'POINT(0 0)') ?>">
        </div>

        <div>
            <label class="block font-bold mb-1">Últim Manteniment</label>
            <input type="date" name="last_maintenance" class="w-full px-3 py-1.5 border rounded-lg" value="<?= htmlspecialchars($vehicle['last_maintenance'] ?? '') ?>">
        </div>

        <div>
            <label class="block font-bold mb-1">Dades Sensor (JSON)</label>
            <textarea name="sensor_data" rows="4" class="w-full px-3 py-1.5 border rounded-lg"><?= htmlspecialchars($vehicle['sensor_data'] ?? '{}') ?></textarea>
        </div>

        <div class="flex gap-4 pt-4 justify-center">
            <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 px-6 rounded-lg">Actualizar</button>
            <a href="/vehicles" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded-lg">Cancel·lar</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>