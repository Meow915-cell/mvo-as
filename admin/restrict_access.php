<?php
// restrict_access.php
function restrictAccess($conn, $user_id, $module) {
    // Fetch user permissions
    $stmt = $conn->prepare("SELECT permissions FROM users WHERE id = ? AND role = 'admin'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $permissions = json_decode($result['permissions'] ?? '{}', true);
    $stmt->close();

    // Check read and write permissions for the module
    $has_read_access = isset($permissions[$module]['read']) && $permissions[$module]['read'] === 'on';
    $has_write_access = isset($permissions[$module]['write']) && $permissions[$module]['write'] === 'on';

    // Return access decisions
    return [
        'has_read_access' => $has_read_access,
        'has_write_access' => $has_write_access,
        'permissions' => $permissions
    ];
}
?>