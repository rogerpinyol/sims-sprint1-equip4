
-- Tenants
INSERT INTO tenants (name, subdomain, plan_type, api_key, is_active)
VALUES
  ('Acme Mobility', 'acme', 'standard', 'ACME_KEY_123', 1),
  ('Beta Transport', 'beta', 'premium', 'BETA_KEY_456', 1);

-- Managers
INSERT INTO users (tenant_id, email, password_hash, role, name, is_active)
VALUES
  (1, 'manager@acme.test', '$2y$10$abcdefghijklmnopqrstuv', 'manager', 'Acme Manager', 1),
  (2, 'manager@beta.test', '$2y$10$abcdefghijklmnopqrstuv', 'manager', 'Beta Manager', 1);

-- Clients
INSERT INTO users (tenant_id, email, password_hash, role, name, is_active)
VALUES
  (1, 'client1@acme.test', '$2y$10$abcdefghijklmnopqrstuv', 'client', 'Acme Client 1', 1),
  (2, 'client1@beta.test', '$2y$10$abcdefghijklmnopqrstuv', 'client', 'Beta Client 1', 1);

-- Vehicles (POINT(lng, lat))
INSERT INTO vehicles (tenant_id, vin, model, battery_capacity, status, location, sensor_data)
VALUES
  (1, 'ACMEVIN000000001', 'E-Scooter A1', 1.2, 'available', POINT(-3.7038, 40.4168), '{"battery":85}'),
  (1, 'ACMEVIN000000002', 'E-Bike B2', 0.8, 'maintenance', POINT(-3.69, 40.42), '{"battery":60}'),
  (2, 'BETAVIN000000001', 'E-Scooter Z1', 1.1, 'booked', POINT(2.1734, 41.3851), '{"battery":45}'),
  (2, 'BETAVIN000000002', 'E-Car C3', 40.0, 'charging', POINT(2.18, 41.39), '{"battery":72}');

-- Note: password_hash values are placeholders. Replace with real bcrypt hashes.
