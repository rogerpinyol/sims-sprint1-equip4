<?php 
require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/navbar.php'; 
?>

<div class="container mx-auto p-4 max-w-2xl">
    <h1 class="text-3xl font-bold mb-6 text-orange-600 text-center">Afegir Nou Vehicle</h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="bg-orange-100 border border-orange-400 text-orange-700 px-4 py-3 rounded mb-6">
            <?= htmlspecialchars($_SESSION['error']) ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="bg-orange-100 border border-orange-400 text-orange-700 px-4 py-3 rounded mb-6">
            <?= htmlspecialchars($_SESSION['success']) ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form action="/vehicle/store" method="POST" class="bg-white shadow-lg rounded-lg p-4 space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block font-bold mb-1">VIN (17 caràcters)</label>
                <input type="text" name="vin" maxlength="17" required class="w-full px-3 py-1.5 border rounded-lg focus:ring-2 focus:ring-orange-500" placeholder="WF0XXXXXXXXXXXXXX">
            </div>
            <div>
                <label class="block font-bold mb-1">Model</label>
                <input type="text" name="model" required class="w-full px-3 py-1.5 border rounded-lg focus:ring-2 focus:ring-orange-500" placeholder="Model 3">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block font-bold mb-1">Capacitat Bateria (kWh)</label>
                <input type="number" step="0.1" name="battery_capacity" required class="w-full px-3 py-1.5 border rounded-lg" placeholder="75">
            </div>
            <div>
                <label class="block font-bold mb-1">Estat</label>
                <select name="status" required class="w-full px-3 py-1.5 border rounded-lg">
                    <option value="available">Disponible</option>
                    <option value="charging">Carregant</option>
                    <option value="booked">Reservat</option>
                    <option value="maintenance">Manteniment</option>
                    <option value="offline">Fora de servei</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block font-bold mb-1">Ubicació (POINT(lat lon))</label>
            <input type="text" name="location" required class="w-full px-3 py-1.5 border rounded-lg" placeholder="POINT(41.3851 2.1734)">
        </div>

        <div>
            <label class="block font-bold mb-1">Últim Manteniment (YYYY-MM-DD)</label>
            <input type="date" name="last_maintenance" class="w-full px-3 py-1.5 border rounded-lg">
        </div>

        <div>
            <label class="block font-bold mb-1">Dades Sensor (JSON)</label>
            <textarea name="sensor_data" rows="4" class="w-full px-3 py-1.5 border rounded-lg" placeholder='{"temp": 25, "battery_level": 90}'></textarea>
        </div>

        <div class="flex gap-4 pt-4 justify-center">
            <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white font-semibold py-2 px-6 rounded-lg transition">
                Guardar Vehicle
            </button>
            <a href="/vehicles" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-6 rounded-lg transition">
                Cancel·lar
            </a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../partials/footer.php'; ?>