CREATE TABLE tenants (
	id INT AUTO_INCREMENT PRIMARY KEY,
	name VARCHAR(255) NOT NULL,
	subdomain VARCHAR(100) UNIQUE,
	plan_type ENUM('standard', 'premium', 'enterprise') DEFAULT 'standard',
	api_key VARCHAR(255) UNIQUE,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	is_active BOOLEAN DEFAULT 1
);

CREATE TABLE users (
	id INT AUTO_INCREMENT PRIMARY KEY,
	tenant_id INT NOT NULL,
	email VARCHAR(255) UNIQUE NOT NULL,
	password_hash VARCHAR(255) NOT NULL,
	role ENUM('client', 'manager', 'tenant_admin') DEFAULT 'client',
	name VARCHAR(100),
	phone VARCHAR(30),
	accessibility_flags JSON,
	is_active BOOLEAN DEFAULT 1,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	INDEX idx_tenant_role (tenant_id, role),
	FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE vehicles (
	id INT AUTO_INCREMENT PRIMARY KEY,
	tenant_id INT NOT NULL,
	vin VARCHAR(17) UNIQUE NOT NULL,
	model VARCHAR(100),
	battery_capacity DECIMAL(5,2),
	status ENUM('available', 'booked', 'maintenance', 'charging') DEFAULT 'available',
	location POINT NOT NULL,
	last_maintenance DATE,
	sensor_data JSON,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	SPATIAL INDEX idx_location (location),
	INDEX idx_tenant_status (tenant_id, status),
	FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE bookings (
	id INT AUTO_INCREMENT PRIMARY KEY,
	tenant_id INT NOT NULL,
	user_id INT,
	vehicle_id INT,
	start_time DATETIME NOT NULL,
	end_time DATETIME NOT NULL,
	total_cost DECIMAL(10,2),
	payment_method ENUM('pay_per_use', 'subscription'),
	status ENUM('pending', 'active', 'completed', 'cancelled') DEFAULT 'pending',
	INDEX idx_tenant_user_start (tenant_id, user_id, start_time),
	FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
	FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE RESTRICT
);

CREATE TABLE locations (
	id INT AUTO_INCREMENT PRIMARY KEY,
	tenant_id INT NOT NULL,
	name VARCHAR(255),
	coords POINT NOT NULL,
	type ENUM('city', 'airport', 'park'),
	SPATIAL INDEX idx_coords (coords),
	INDEX idx_tenant_type (tenant_id, type),
	FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE maintenance (
	id INT AUTO_INCREMENT PRIMARY KEY,
	tenant_id INT NOT NULL,
	vehicle_id INT,
	type ENUM('charge', 'repair'),
	scheduled_at DATETIME,
	completed_at DATETIME,
	notes TEXT,
	INDEX idx_tenant_vehicle_scheduled (tenant_id, vehicle_id, scheduled_at),
	FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
	FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
);

CREATE TABLE payments (
	id INT AUTO_INCREMENT PRIMARY KEY,
	tenant_id INT NOT NULL,
	booking_id INT,
	amount DECIMAL(10,2),
	method ENUM('card', 'subscription'),
	status ENUM('paid', 'pending'),
	FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
	FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);

CREATE TABLE subscriptions (
	id INT AUTO_INCREMENT PRIMARY KEY,
	tenant_id INT NOT NULL,
	user_id INT,
	plan_type ENUM('monthly', 'annual'),
	expiry DATETIME,
	status ENUM('active', 'expired') DEFAULT 'active',
	INDEX idx_tenant_user (tenant_id, user_id),
	FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE support_tickets (
	id INT AUTO_INCREMENT PRIMARY KEY,
	tenant_id INT NOT NULL,
	user_id INT,
	description TEXT,
	status ENUM('open', 'in_progress', 'resolved') DEFAULT 'open',
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	INDEX idx_tenant_status (tenant_id, status),
	FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE partners (
	id INT AUTO_INCREMENT PRIMARY KEY,
	tenant_id INT NOT NULL,
	name VARCHAR(255),
	contract_details JSON,
	created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE ads (
	id INT AUTO_INCREMENT PRIMARY KEY,
	tenant_id INT NOT NULL,
	content JSON,
	placement ENUM('app', 'vehicle'),
	status ENUM('active', 'inactive') DEFAULT 'active',
	INDEX idx_tenant_status (tenant_id, status),
	FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE settings (
	id INT AUTO_INCREMENT PRIMARY KEY,
	tenant_id INT NOT NULL,
	`key` VARCHAR(100) NOT NULL,
	value JSON,
	INDEX idx_tenant_key (tenant_id, `key`),
	FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
);

CREATE TABLE incidences (
	id INT AUTO_INCREMENT PRIMARY KEY,
	tenant_id INT NOT NULL,
	vehicle_id INT,
	user_id INT,
	type ENUM('breakdown', 'warning', 'accident', 'iot_error', 'other'),
	description TEXT,
	status ENUM('open', 'in_progress', 'resolved') DEFAULT 'open',
	reported_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	resolved_at TIMESTAMP NULL,
	priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
	notes JSON,
	INDEX idx_tenant_status (tenant_id, status),
	INDEX idx_tenant_vehicle_reported (tenant_id, vehicle_id, reported_at),
	FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
	FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
	FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
