<?php
/**
 * Property-Based Tests for User Management
 * 
 * **Feature: multi-company-hrms, Property 6: User-Company-Role Invariant**
 * **Validates: Requirements 3.2, 8.1**
 * 
 * For any user record, the company_id and role_id fields SHALL be non-null
 * and reference valid records in their respective tables.
 * 
 * **Feature: multi-company-hrms, Property 5: Password Hash Round-Trip**
 * **Validates: Requirements 3.1**
 * 
 * For any valid password string, hashing with bcrypt and then verifying
 * the original password against the hash SHALL return true.
 */

namespace HRMS\Tests\Property;

use HRMS\Tests\TestCase;
use Eris\Generator;
use Eris\TestTrait;

class UserTest extends TestCase
{
    use TestTrait;
    
    /**
     * Property 6: All users should have valid company_id and role_id.
     * 
     * @test
     */
    public function allUsersHaveValidCompanyAndRole(): void
    {
        $users = $this->query('SELECT id, company_id, role_id FROM users');
        
        if (empty($users)) {
            $this->markTestSkipped('No users in database');
        }
        
        foreach ($users as $user) {
            // company_id should not be null
            $this->assertNotNull(
                $user['company_id'],
                "User {$user['id']} should have a company_id"
            );
            
            // role_id should not be null
            $this->assertNotNull(
                $user['role_id'],
                "User {$user['id']} should have a role_id"
            );
            
            // company_id should reference a valid company
            $company = $this->queryOne(
                'SELECT id FROM companies WHERE id = ?',
                [$user['company_id']]
            );
            $this->assertNotNull(
                $company,
                "User {$user['id']} should reference a valid company"
            );
            
            // role_id should reference a valid role
            $role = $this->queryOne(
                'SELECT id FROM roles WHERE id = ?',
                [$user['role_id']]
            );
            $this->assertNotNull(
                $role,
                "User {$user['id']} should reference a valid role"
            );
        }
    }
    
    /**
     * Property 6: For any randomly selected user, company_id and role_id are valid.
     * 
     * @test
     */
    public function randomUserHasValidCompanyAndRole(): void
    {
        $users = $this->query('SELECT id, company_id, role_id FROM users');
        
        if (empty($users)) {
            $this->markTestSkipped('No users in database');
        }
        
        $this->forAll(Generator\choose(0, count($users) - 1))
        ->withMaxSize(100)
        ->then(function ($userIndex) use ($users) {
            $user = $users[$userIndex];
            
            // Verify company exists
            $company = $this->queryOne(
                'SELECT id FROM companies WHERE id = ?',
                [$user['company_id']]
            );
            
            $this->assertNotNull($company, 'User company_id should reference valid company');
            
            // Verify role exists
            $role = $this->queryOne(
                'SELECT id FROM roles WHERE id = ?',
                [$user['role_id']]
            );
            
            $this->assertNotNull($role, 'User role_id should reference valid role');
        });
    }
    
    /**
     * Property 5: Password hash round-trip verification.
     * 
     * @test
     */
    public function passwordHashRoundTrip(): void
    {
        $this->forAll(
            Generator\string(),
            Generator\choose(8, 72) // bcrypt max length is 72
        )
        ->withMaxSize(100)
        ->then(function ($randomChars, $length) {
            // Generate a password of specified length
            $password = substr(str_repeat($randomChars . 'aA1!', $length), 0, $length);
            
            if (strlen($password) < 1) {
                $password = 'testpassword123';
            }
            
            // Hash the password
            $hash = password_hash($password, PASSWORD_BCRYPT);
            
            // Verify the password against the hash
            $this->assertTrue(
                password_verify($password, $hash),
                'Password should verify against its own hash'
            );
            
            // Verify wrong password fails
            $wrongPassword = $password . 'wrong';
            $this->assertFalse(
                password_verify($wrongPassword, $hash),
                'Wrong password should not verify'
            );
        });
    }
    
    /**
     * Property 5: All stored password hashes should be valid bcrypt hashes.
     * 
     * @test
     */
    public function allStoredPasswordHashesAreValidBcrypt(): void
    {
        $users = $this->query('SELECT id, password_hash FROM users LIMIT 100');
        
        if (empty($users)) {
            $this->markTestSkipped('No users in database');
        }
        
        foreach ($users as $user) {
            // Bcrypt hashes start with $2y$ or $2a$ or $2b$
            $this->assertMatchesRegularExpression(
                '/^\$2[ayb]\$/',
                $user['password_hash'],
                "User {$user['id']} should have a valid bcrypt hash"
            );
            
            // Bcrypt hashes are 60 characters
            $this->assertEquals(
                60,
                strlen($user['password_hash']),
                "User {$user['id']} password hash should be 60 characters"
            );
        }
    }
    
    /**
     * Property: User emails should be unique.
     * 
     * @test
     */
    public function allUserEmailsAreUnique(): void
    {
        $users = $this->query('SELECT id, email FROM users');
        
        if (count($users) < 2) {
            $this->markTestSkipped('Need at least 2 users to test uniqueness');
        }
        
        $emails = array_column($users, 'email');
        $uniqueEmails = array_unique($emails);
        
        $this->assertCount(
            count($emails),
            $uniqueEmails,
            'All user emails should be unique'
        );
    }
    
    /**
     * Property: User status should be a valid enum value.
     * 
     * @test
     */
    public function allUserStatusesAreValid(): void
    {
        $validStatuses = ['active', 'inactive', 'locked'];
        
        $users = $this->query('SELECT id, status FROM users');
        
        foreach ($users as $user) {
            $this->assertContains(
                $user['status'],
                $validStatuses,
                "User {$user['id']} should have a valid status"
            );
        }
    }
}
