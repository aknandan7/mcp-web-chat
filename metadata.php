<?php
#################################################################
                    # table-column mapping
#################################################################
return [
    'emp_personal_info' => ['indo_code','resource_name', 'gender', 'dob', 'designation', 'department','email_id'],
    'emp_salary_info'   => ['indo_code','salary_month','monthly_ctc'],
    'emp_holidays_info' => ['id','holiday_id','company_id','circle_id','name','type','date','datetime'],
    'emp_leave_info'  =>  ['leave_id','indo_code','old_indocode','lt_id','duration_id','from_date','to_date','leave_period','comments','status','manager_emailid','managerRemark','approveddate','approvedby','applydatetime','applyby'],
    'emp_leavequota_info' => ['lq_id','agencyid','companyid','indo_code','old_indocode','lt_id','leaves','leavesTaked','lastIncreaseDate','lastIncreaseLeaves','alloteddate','allotedby'],
    // add other tables & columns
];

?>
