<?php
// FILE: /public/index.php

/**
 * SplashLeadRouter - Entry Point
 * All requests are routed through this file
 */

// Start session
session_start();

// Set timezone to UTC
date_default_timezone_set('UTC');

// Load core classes
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/core/View.php';

// Load helper classes
require_once __DIR__ . '/../app/helpers/Validator.php';
require_once __DIR__ . '/../app/helpers/CSRF.php';
require_once __DIR__ . '/../app/helpers/FileUpload.php';
require_once __DIR__ . '/../app/helpers/Email.php';

// Initialize router
$router = new Router();

// =====================================================
// PUBLIC ROUTES
// =====================================================

// Home - redirect to login or dashboard
$router->get('/', 'DashboardController', 'index', 'home');

// Authentication routes
$router->get('/login', 'AuthController', 'showLogin', 'login.show');
$router->post('/login', 'AuthController', 'login', 'login.post');
$router->get('/logout', 'AuthController', 'logout', 'logout');
$router->get('/register', 'AuthController', 'showRegister', 'register.show');
$router->post('/register', 'AuthController', 'register', 'register.post');

// =====================================================
// DASHBOARD ROUTES
// =====================================================

$router->get('/dashboard', 'DashboardController', 'index', 'dashboard');

// =====================================================
// LEAD ROUTES
// =====================================================

$router->get('/leads', 'LeadController', 'index', 'leads.index');
$router->get('/leads/create', 'LeadController', 'create', 'leads.create');
$router->post('/leads', 'LeadController', 'store', 'leads.store');
$router->get('/leads/:id', 'LeadController', 'show', 'leads.show');
$router->get('/leads/:id/edit', 'LeadController', 'edit', 'leads.edit');
$router->post('/leads/:id/update', 'LeadController', 'update', 'leads.update');
$router->post('/leads/:id/status', 'LeadController', 'updateStatus', 'leads.status');
$router->post('/leads/:id/notes', 'LeadController', 'addNote', 'leads.notes');
$router->post('/leads/:id/reassign', 'LeadController', 'reassign', 'leads.reassign');

// =====================================================
// AGENT ROUTES
// =====================================================

$router->get('/agents', 'AgentController', 'index', 'agents.index');
$router->get('/agents/create', 'AgentController', 'create', 'agents.create');
$router->post('/agents', 'AgentController', 'store', 'agents.store');
$router->get('/agents/:id', 'AgentController', 'show', 'agents.show');
$router->get('/agents/:id/edit', 'AgentController', 'edit', 'agents.edit');
$router->post('/agents/:id/update', 'AgentController', 'update', 'agents.update');
$router->post('/agents/:id/delete', 'AgentController', 'delete', 'agents.delete');

// =====================================================
// ZONE ROUTES
// =====================================================

$router->get('/zones', 'ZoneController', 'index', 'zones.index');
$router->get('/zones/create', 'ZoneController', 'create', 'zones.create');
$router->post('/zones', 'ZoneController', 'store', 'zones.store');
$router->get('/zones/:id', 'ZoneController', 'show', 'zones.show');
$router->get('/zones/:id/edit', 'ZoneController', 'edit', 'zones.edit');
$router->post('/zones/:id/update', 'ZoneController', 'update', 'zones.update');
$router->post('/zones/:id/assign-agent', 'ZoneController', 'assignAgent', 'zones.assign');
$router->post('/zones/:id/remove-agent/:agentId', 'ZoneController', 'removeAgent', 'zones.remove');
$router->post('/zones/:id/delete', 'ZoneController', 'delete', 'zones.delete');

// =====================================================
// ROUTING RULE ROUTES
// =====================================================

$router->get('/routing-rules', 'RoutingRuleController', 'index', 'routing.index');
$router->get('/routing-rules/create', 'RoutingRuleController', 'create', 'routing.create');
$router->post('/routing-rules', 'RoutingRuleController', 'store', 'routing.store');
$router->get('/routing-rules/:id', 'RoutingRuleController', 'show', 'routing.show');
$router->get('/routing-rules/:id/edit', 'RoutingRuleController', 'edit', 'routing.edit');
$router->post('/routing-rules/:id/update', 'RoutingRuleController', 'update', 'routing.update');
$router->post('/routing-rules/:id/toggle', 'RoutingRuleController', 'toggleStatus', 'routing.toggle');
$router->post('/routing-rules/:id/delete', 'RoutingRuleController', 'delete', 'routing.delete');

// =====================================================
// API ROUTES
// =====================================================

$router->get('/api/docs', 'ApiController', 'docs', 'api.docs');
$router->post('/api/leads', 'ApiController', 'createLead', 'api.leads.create');
$router->post('/api/leads/:id', 'ApiController', 'updateLead', 'api.leads.update');
$router->get('/api/leads/:id', 'ApiController', 'getLead', 'api.leads.get');

// =====================================================
// DISPATCH REQUEST
// =====================================================

$router->dispatch();
