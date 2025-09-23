<?php
return [
    # emp_personal_info
    'resource_name' => ['name', 'employee name'],
    'gender'        => ['gender', 'sex'],
    'dob'           => ['dob', 'birth date'],
    'designation'   => ['designation', 'role'],
    'department'    => ['department', 'team'],
    'email_id'      => ['email', 'email id'],

    # emp_salary_info
    'salary_month'  => ['salary month', 'month'],
    'monthly_ctc'   => ['salary', 'monthly salary'],

    # emp_holidays_info
    'name'          => ['holiday name','holiday list','festival'],
    'type'          => ['holiday type', 'leave type'],
    'date'          => ['holiday date', 'festival date'],

    # emp_leave_info
    'from_date'     => ['leave from', 'start date'],
    'to_date'       => ['leave to', 'end date'],
    'comments'      => ['leave reason', 'comments'],
    'status'        => ['leave status', 'approval'],
    'managerRemark' => ['remark', 'manager comment'],

    # emp_leavequota_info
    'leaves'        => ['leave balance', 'total leaves','leave quota'],
    'lt_id'         => ['leave type', 'leave category'],
    'leavesTaked'   => ['leaves taken', 'used leaves']
];
