<?php

return [
    // General
    'app.name' => 'EcoMotion',
    'locale.en' => 'English',
    'locale.ca' => 'Catalan',
    'common.logo_alt' => 'EcoMotion logo',

    // Home
    'home.title' => 'Welcome - EcoMotion Platform',
    'home.heading' => 'Welcome to EcoMotion Platform',
    'home.description' => 'This is a multi-tenant SaaS platform for vehicle fleet management.',
    'home.link.super_admin_dashboard' => 'Super Admin Dashboard (Tenants Management)',

    // Errors
    'error.title' => 'Error',
    'error.default_message' => 'An error occurred.',
    'error.back_to_tenants' => 'Back to Tenants',

    // Navbar
    'navbar.add_vehicle' => 'Add Vehicle',
    'navbar.vehicles' => 'Vehicles',
    'navbar.close_alert' => 'Close alert',

    // Footer
    'footer.copyright' => '© {year} EcoMotion. All rights reserved.',
    'footer.modal.title' => 'Are you sure?',
    'footer.modal.initial_body' => 'This item will be deleted permanently.',
    'footer.modal.confirm_body' => 'Are you sure you want to delete "{name}"? This action cannot be undone.',
    'footer.modal.cancel' => 'Cancel',
    'footer.modal.confirm' => 'Delete',
    'footer.modal.default_item' => 'vehicle',

    // Forms
    'form.email' => 'Email',
    'form.email_placeholder' => 'you@example.com',
    'form.password' => 'Password',
    'form.password_placeholder' => 'Enter your password',
    'form.full_name' => 'Full name',
    'form.full_name_placeholder' => 'Jane Doe',
    'form.full_name_title' => 'Only letters and spaces',
    'form.work_email' => 'Work email',
    'form.work_email_placeholder' => 'name@company.com',

    // Auth - Client Login
    'auth.login.heading' => 'Client Access',
    'auth.common.success' => 'Account created successfully! You can now sign in.',
    'auth.common.sign_in' => 'Sign in',
    'auth.login.no_account' => "Don't have an account?",
    'auth.login.register_link' => 'Register here',

    // Auth - Manager Login
    'auth.manager.heading' => 'Manager sign in',

    // Auth - Tenant Admin Login
    'auth.tenant_admin.heading' => 'Tenant admin sign in',

    // Auth - Validation
    'auth.validation.enter_valid_email' => 'Enter a valid email.',
    'auth.validation.password_required' => 'Password required.',

    // Auth - Register
    'auth.register.heading' => 'Create your account',
    'auth.register.subtitle' => 'Start your free trial — no credit card required',
    'auth.register.fix_errors' => 'Please fix the following:',
    'auth.register.submit' => 'Create account',
    'auth.register.has_account' => 'Already have an account?',
    'auth.register.sign_in' => 'Sign in',

    // Common links
    'common.privacy' => 'Privacy',
    'common.terms' => 'Terms',

    // Landing page
    'landing.meta_title' => 'EcoMotion - Fleet Management SaaS for Companies',
    'landing.meta_description' => 'EcoMotion is the all-in-one platform for businesses to manage, optimize, and scale their vehicle fleets.',
    'landing.header.login' => 'Log in',
    'landing.header.contact' => 'Contact',
    
    'landing.hero.title' => 'Fleet Management SaaS for Companies',
    'landing.hero.description' => 'EcoMotion is the all-in-one platform for businesses to manage, optimize, and scale their vehicle fleets. Access your company dashboard or contact our team to get started.',
    'landing.hero.features_btn' => 'Features',
    'landing.hero.contact_btn' => 'Contact Admins',
    'landing.hero.dashboard_preview_alt' => 'Dashboard preview',
    
    'landing.features.title' => 'Everything your company needs to run a modern fleet',
    'landing.features.description' => 'From real-time tracking to automated maintenance and unified billing—EcoMotion is your single source of truth for fleet operations.',
    'landing.features.tracking.title' => 'Live vehicle tracking',
    'landing.features.tracking.description' => 'Monitor location, battery health, and status in real time across your entire fleet.',
    'landing.features.scheduling.title' => 'Smart scheduling',
    'landing.features.scheduling.description' => 'Optimize bookings and dispatch with rules that balance utilization and battery charging.',
    'landing.features.maintenance.title' => 'Maintenance automation',
    'landing.features.maintenance.description' => 'Forecast service intervals and trigger workflows before issues impact operations.',
    'landing.features.billing.title' => 'Unified billing',
    'landing.features.billing.description' => 'Consolidate pay-per-use, subscriptions, and partner invoices in one clean ledger.',
    'landing.features.analytics.title' => 'Analytics & reporting',
    'landing.features.analytics.description' => 'Understand costs, utilization, and performance with prebuilt and custom reports.',
    'landing.features.api.title' => 'Developer-friendly',
    'landing.features.api.description' => 'Robust API and webhooks to integrate EcoMotion into your existing stack.',
    
    'landing.contact.title' => 'Contact Administrators',
    'landing.contact.name_label' => 'Name',
    'landing.contact.email_label' => 'Email',
    'landing.contact.message_label' => 'Message',
    'landing.contact.submit_btn' => 'Send Message',
    
    'landing.footer.copyright' => '© {year} EcoMotion. All rights reserved.',
    'landing.footer.privacy' => 'Privacy',
    'landing.footer.terms' => 'Terms',
    'landing.footer.contact' => 'Contact',
    
    // Admin - Tenants list
    'admin.tenants.meta_title' => 'Tenants Management - Tenant Admin',
    'admin.tenants.heading' => 'Tenants Management',
    'admin.tenants.flash.api_key_label' => 'API Key:',
    'admin.tenants.search.placeholder' => 'Search by name or subdomain',
    'admin.tenants.filter.plan.all' => 'All plans',
    'admin.tenants.filter.plan.standard' => 'Standard',
    'admin.tenants.filter.plan.premium' => 'Premium',
    'admin.tenants.filter.status.all' => 'All status',
    'admin.tenants.filter.status.active' => 'Active',
    'admin.tenants.filter.status.inactive' => 'Inactive',
    'admin.tenants.filter.submit' => 'Filter',
    'admin.tenants.table.id' => 'ID',
    'admin.tenants.table.name' => 'Name',
    'admin.tenants.table.subdomain' => 'Subdomain',
    'admin.tenants.table.plan' => 'Plan',
    'admin.tenants.table.status' => 'Status',
    'admin.tenants.table.actions' => 'Actions',
    'admin.tenants.table.empty' => 'No tenants found.',
    'admin.tenants.pagination.summary' => 'Showing {limit} per page, offset {offset}',
    'admin.tenants.actions.view' => 'View',
    'admin.tenants.actions.edit' => 'Edit',
    'admin.tenants.actions.deactivate' => 'Deactivate',
    'admin.tenants.actions.activate' => 'Activate',
    'admin.tenants.actions.rotate_api_key' => 'Rotate API Key',
    'admin.tenants.confirm.deactivate' => 'Deactivate this tenant?',
    'admin.tenants.confirm.activate' => 'Activate this tenant?',
    'admin.tenants.confirm.rotate_api_key' => 'Rotate API key for this tenant?',
    'admin.tenants.modal.title' => 'Create new tenant',
    'admin.tenants.modal.name_label' => 'Name',
    'admin.tenants.modal.subdomain_label' => 'Subdomain',
    'admin.tenants.modal.plan_type_label' => 'Plan type',
    'admin.tenants.modal.plan_standard' => 'Standard',
    'admin.tenants.modal.plan_premium' => 'Premium',
    'admin.tenants.modal.cancel' => 'Cancel',
    'admin.tenants.modal.submit' => 'Create tenant',
    'admin.tenants.footer' => 'EcoMotion © {year} | Tenant Admin',

    // Admin - Tenant detail/edit
    'admin.tenant_show.meta_title' => 'Tenant details',
    'admin.tenant_show.back_to_tenants' => '← Back to tenants',
    'admin.tenant_show.heading' => 'Tenant #{id}',
    'admin.tenant_show.name' => 'Name:',
    'admin.tenant_show.subdomain' => 'Subdomain:',
    'admin.tenant_show.plan' => 'Plan:',
    'admin.tenant_show.active' => 'Active:',
    'admin.tenant_show.active_yes' => 'Yes',
    'admin.tenant_show.active_no' => 'No',
    'admin.tenant_show.created' => 'Created:',
    'admin.tenant_show.edit' => 'Edit',
    'admin.tenant_show.rotate_api_key' => 'Rotate API Key',

    'admin.tenant_edit.meta_title' => 'Edit tenant',
    'admin.tenant_edit.back_to_tenants' => '← Back to tenants',
    'admin.tenant_edit.heading' => 'Edit tenant #{id}',
    'admin.tenant_edit.name_label' => 'Name',
    'admin.tenant_edit.subdomain_label' => 'Subdomain',
    'admin.tenant_edit.plan_type_label' => 'Plan type',
    'admin.tenant_edit.plan_standard' => 'Standard',
    'admin.tenant_edit.plan_premium' => 'Premium',
    'admin.tenant_edit.is_active_label' => 'Active',
    'admin.tenant_edit.is_active_yes' => 'Yes',
    'admin.tenant_edit.is_active_no' => 'No',
    'admin.tenant_edit.save' => 'Save',
    'admin.tenant_edit.cancel' => 'Cancel',

    // Manager - Dashboard
    'manager.dashboard.overview' => 'Overview',
    'manager.dashboard.stats.total_vehicles' => 'Available vehicles',
    'manager.dashboard.stats.active_reservations' => 'Active reservations',
    'manager.dashboard.stats.daily_revenue' => 'Daily revenue',
    'manager.dashboard.vehicles.title' => 'Vehicles',
    'manager.dashboard.map.title' => 'Vehicles map',
    'manager.footer.version' => 'EcoMotion © {year} | Version {version}',

    // Manager - Users list
    'manager.users.heading' => 'User management',
    'manager.users.flash.success' => 'Operation completed successfully.',
    'manager.users.form.name_placeholder' => 'Name',
    'manager.users.form.email_placeholder' => 'Email',
    'manager.users.form.password_placeholder' => 'Password',
    'manager.users.form.phone_placeholder' => 'Phone',
    'manager.users.form.accessibility_placeholder' => 'Accessibility',
    'manager.users.form.role.client' => 'Client',
    'manager.users.form.role.manager' => 'Manager',
    'manager.users.form.submit' => 'Create user',
    'manager.users.table.empty' => 'No users found.',
    'manager.users.table.id' => 'ID',
    'manager.users.table.name' => 'Name',
    'manager.users.table.email' => 'Email',
    'manager.users.table.role' => 'Role',
    'manager.users.table.phone' => 'Phone',
    'manager.users.table.accessibility' => 'Acces.',
    'manager.users.table.actions' => 'Actions',
    'manager.users.table.actions.save' => 'Save',
    'manager.users.table.actions.delete' => 'Delete',
    'manager.users.table.actions.none' => '—',

    // Manager - Create user partial
    'manager.users.create.heading' => 'Create user',
    'manager.users.create.name_label' => 'Name',
    'manager.users.create.email_label' => 'Email',
    'manager.users.create.password_label' => 'Password',
    'manager.users.create.phone_label' => 'Phone',
    'manager.users.create.role_label' => 'Role',
    'manager.users.create.submit' => 'Create',
];
