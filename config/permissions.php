<?php
/**
 * Permissions Configuration
 * 
 * Defines all permission constants and endpoint mappings.
 */

return [
    // Employee permissions
    'employee.view' => 'View all employees',
    'employee.view_own' => 'View own profile',
    'employee.create' => 'Create employees',
    'employee.update' => 'Update employees',
    'employee.update_own' => 'Update own profile',
    'employee.delete' => 'Delete employees',
    
    // Attendance permissions
    'attendance.view' => 'View all attendance records',
    'attendance.view_own' => 'View own attendance',
    'attendance.clock' => 'Clock in/out',
    'attendance.create' => 'Create attendance records',
    'attendance.update' => 'Update attendance records',
    
    // Leave permissions
    'leave.view' => 'View all leave requests',
    'leave.view_own' => 'View own leave requests',
    'leave.request' => 'Submit leave requests',
    'leave.approve' => 'Approve/reject leave requests',
    
    // Payroll permissions
    'payroll.view' => 'View all payroll records',
    'payroll.view_own' => 'View own payroll',
    'payroll.create' => 'Process payroll',
    
    // Role-based permission sets
    'roles' => [
        'Admin' => [
            'employee.view', 'employee.view_own', 'employee.create', 'employee.update', 'employee.update_own', 'employee.delete',
            'attendance.view', 'attendance.view_own', 'attendance.clock', 'attendance.create', 'attendance.update',
            'leave.view', 'leave.view_own', 'leave.request', 'leave.approve',
            'payroll.view', 'payroll.view_own', 'payroll.create'
        ],
        'Employee' => [
            'employee.view_own', 'employee.update_own',
            'attendance.view_own', 'attendance.clock',
            'leave.view_own', 'leave.request',
            'payroll.view_own'
        ]
    ]
];
