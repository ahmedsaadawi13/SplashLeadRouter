<?php
// FILE: /tests/run_tests.php

/**
 * SplashLeadRouter - Basic Test Runner
 * Run: php tests/run_tests.php
 */

echo "===========================================\n";
echo "SplashLeadRouter - Test Suite\n";
echo "===========================================\n\n";

$passed = 0;
$failed = 0;

// Test 1: Database Connection
echo "[TEST] Database Connection... ";
try {
    require_once __DIR__ . '/../app/core/Database.php';
    $db = Database::getInstance();
    $conn = $db->getConnection();
    if ($conn) {
        echo "✓ PASSED\n";
        $passed++;
    } else {
        echo "✗ FAILED\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 2: User Model
echo "[TEST] User Model - Find by Email... ";
try {
    require_once __DIR__ . '/../app/core/Model.php';
    require_once __DIR__ . '/../app/models/User.php';
    $userModel = new User();
    $user = $userModel->findByEmail('admin@splashleadrouter.com');
    if ($user && $user['email'] === 'admin@splashleadrouter.com') {
        echo "✓ PASSED\n";
        $passed++;
    } else {
        echo "✗ FAILED\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 3: Lead Model
echo "[TEST] Lead Model - Count by Tenant... ";
try {
    require_once __DIR__ . '/../app/models/Lead.php';
    $leadModel = new Lead();
    $count = $leadModel->countByTenant(1, []);
    if (is_numeric($count)) {
        echo "✓ PASSED (Found $count leads)\n";
        $passed++;
    } else {
        echo "✗ FAILED\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 4: Zone Model
echo "[TEST] Zone Model - Get Zones by Tenant... ";
try {
    require_once __DIR__ . '/../app/models/Zone.php';
    $zoneModel = new Zone();
    $zones = $zoneModel->getZonesByTenant(1, true);
    if (is_array($zones)) {
        echo "✓ PASSED (Found " . count($zones) . " zones)\n";
        $passed++;
    } else {
        echo "✗ FAILED\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 5: Validator
echo "[TEST] Validator - Required Field... ";
try {
    require_once __DIR__ . '/../app/helpers/Validator.php';
    $validator = new Validator(['name' => '']);
    $validator->required('name');
    if ($validator->fails()) {
        echo "✓ PASSED\n";
        $passed++;
    } else {
        echo "✗ FAILED\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 6: Validator - Email
echo "[TEST] Validator - Email Validation... ";
try {
    $validator = new Validator(['email' => 'invalid-email']);
    $validator->email('email');
    if ($validator->fails()) {
        echo "✓ PASSED\n";
        $passed++;
    } else {
        echo "✗ FAILED\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 7: CSRF Token Generation
echo "[TEST] CSRF Token Generation... ";
try {
    session_start();
    require_once __DIR__ . '/../app/helpers/CSRF.php';
    $token = CSRF::generateToken();
    if (!empty($token) && strlen($token) === 64) {
        echo "✓ PASSED\n";
        $passed++;
    } else {
        echo "✗ FAILED\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 8: Routing Engine
echo "[TEST] Routing Engine Initialization... ";
try {
    require_once __DIR__ . '/../app/helpers/RoutingEngine.php';
    $engine = new RoutingEngine();
    if ($engine) {
        echo "✓ PASSED\n";
        $passed++;
    } else {
        echo "✗ FAILED\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 9: Subscription Model
echo "[TEST] Subscription Model - Get Active Plans... ";
try {
    require_once __DIR__ . '/../app/models/SubscriptionPlan.php';
    $planModel = new SubscriptionPlan();
    $plans = $planModel->getActivePlans();
    if (is_array($plans) && count($plans) >= 3) {
        echo "✓ PASSED (Found " . count($plans) . " plans)\n";
        $passed++;
    } else {
        echo "✗ FAILED\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $failed++;
}

// Test 10: API Key Model
echo "[TEST] API Key Model - Verify Key... ";
try {
    require_once __DIR__ . '/../app/models/ApiKey.php';
    $apiKeyModel = new ApiKey();
    $result = $apiKeyModel->verifyKey('dprop_live_4f8a9b2c1d3e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6');
    if ($result && isset($result['tenant_id'])) {
        echo "✓ PASSED\n";
        $passed++;
    } else {
        echo "✗ FAILED\n";
        $failed++;
    }
} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    $failed++;
}

// Summary
echo "\n===========================================\n";
echo "Test Results:\n";
echo "===========================================\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";
echo "Total:  " . ($passed + $failed) . "\n";
echo "===========================================\n";

if ($failed === 0) {
    echo "✓ All tests passed!\n\n";
    exit(0);
} else {
    echo "✗ Some tests failed!\n\n";
    exit(1);
}
