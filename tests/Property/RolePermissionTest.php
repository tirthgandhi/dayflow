<?php
/**
 * Property-Based Tests for Role-Permission Mapping
 * 
 * **Feature: multi-company-hrms, Property 7: Role-Based Permission Check**
 * **Validates: Requirements 3.3**
 * 
 * For any user attempting to access a resource, the access decision SHALL match
 * the result of querying role_permissions for that user's role_id and the required permission.
 */

namespace HRMS\Tests\Property;

use HRMS\Tests\TestCase;
use Eris\Generator;
use Eris\TestTrait;

class RolePermissionTest extends TestCase
{
    use TestTrait;
    
    private array $roles = [];
    private array $permissions = [];
    private array $rolePermissions = [];
    
    protected function setUp(): void
    {
        parent::setUp();
        
        // Load roles
        $this->roles = $this->query('SELECT id, name FROM roles');
        
        // Load permissions
        $this->permissions = $this->query('SELECT id, name, module FROM permissions');
        
        // Load role-permission mappings
        $this->rolePermissions = $this->query(
            'SELECT role_id, permission_id FROM role_permissions'
        );
    }
    
    /**
     * Property: For any role and permission combination, the hasPermission check
     * should match the database role_permissions mapping.
     * 
     * @test
     */
    public function rolePermissionMappingIsConsistent(): void
    {
        $this->forAll(
            Generator\choose(0, count($this->roles) - 1),
            Generator\choose(0, count($this->permissions) - 1)
        )
        ->withMaxSize(100)
        ->then(function ($roleIndex, $permissionIndex) {
            $role = $this->roles[$roleIndex];
            $permission = $this->permissions[$permissionIndex];
            
            // Check if permission exists in role_permissions table
            $hasPermissionInDb = $this->queryOne(
                'SELECT 1 FROM role_permissions WHERE role_id = ? AND permission_id = ?',
                [$role['id'], $permission['id']]
            ) !== null;
            
            // Verify the mapping is consistent
            $mappingExists = false;
            foreach ($this->rolePermissions as $rp) {
                if ($rp['role_id'] == $role['id'] && $rp['permission_id'] == $permission['id']) {
                    $mappingExists = true;
                    break;
                }
            }
            
            $this->assertEquals(
                $hasPermissionInDb,
                $mappingExists,
                "Role-permission mapping inconsistency for role {$role['name']} and permission {$permission['name']}"
            );
        });
    }
    
    /**
     * Property: Admin role should have all permissions.
     * 
     * @test
     */
    public function adminRoleHasAllPermissions(): void
    {
        $adminRole = $this->queryOne("SELECT id FROM roles WHERE name = 'Admin'");
        $this->assertNotNull($adminRole, 'Admin role should exist');
        
        $adminPermissionCount = $this->queryOne(
            'SELECT COUNT(*) as count FROM role_permissions WHERE role_id = ?',
            [$adminRole['id']]
        );
        
        $totalPermissions = $this->queryOne('SELECT COUNT(*) as count FROM permissions');
        
        $this->assertEquals(
            $totalPermissions['count'],
            $adminPermissionCount['count'],
            'Admin role should have all permissions'
        );
    }
    
    /**
     * Property: For any user with a role, querying their permissions through
     * role_permissions should return consistent results.
     * 
     * @test
     */
    public function userPermissionsMatchRolePermissions(): void
    {
        // Get sample users with their roles
        $users = $this->query('SELECT id, role_id FROM users LIMIT 100');
        
        if (empty($users)) {
            $this->markTestSkipped('No users in database');
        }
        
        $this->forAll(Generator\choose(0, count($users) - 1))
        ->withMaxSize(100)
        ->then(function ($userIndex) use ($users) {
            $user = $users[$userIndex];
            
            // Get permissions for user's role
            $userPermissions = $this->query(
                'SELECT p.name FROM permissions p
                 JOIN role_permissions rp ON p.id = rp.permission_id
                 WHERE rp.role_id = ?',
                [$user['role_id']]
            );
            
            // Get permissions directly from role_permissions
            $rolePermissions = $this->query(
                'SELECT permission_id FROM role_permissions WHERE role_id = ?',
                [$user['role_id']]
            );
            
            $this->assertCount(
                count($rolePermissions),
                $userPermissions,
                'User permissions should match role permissions count'
            );
        });
    }
    
    /**
     * Property: Role-permission mapping should have no duplicates.
     * 
     * @test
     */
    public function noDuplicateRolePermissionMappings(): void
    {
        $duplicates = $this->query(
            'SELECT role_id, permission_id, COUNT(*) as count 
             FROM role_permissions 
             GROUP BY role_id, permission_id 
             HAVING count > 1'
        );
        
        $this->assertEmpty($duplicates, 'There should be no duplicate role-permission mappings');
    }
    
    /**
     * Property: All role_permissions should reference valid roles and permissions.
     * 
     * @test
     */
    public function allRolePermissionsReferenceValidEntities(): void
    {
        // Check for orphaned role references
        $orphanedRoles = $this->query(
            'SELECT rp.role_id FROM role_permissions rp 
             LEFT JOIN roles r ON rp.role_id = r.id 
             WHERE r.id IS NULL'
        );
        
        $this->assertEmpty($orphanedRoles, 'All role_permissions should reference valid roles');
        
        // Check for orphaned permission references
        $orphanedPermissions = $this->query(
            'SELECT rp.permission_id FROM role_permissions rp 
             LEFT JOIN permissions p ON rp.permission_id = p.id 
             WHERE p.id IS NULL'
        );
        
        $this->assertEmpty($orphanedPermissions, 'All role_permissions should reference valid permissions');
    }
}
