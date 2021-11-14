<?php


/*
|--------------------------------------------------------------------------
| Display Debug backtrace
|--------------------------------------------------------------------------
|
| If set to TRUE, a backtrace will be displayed along with php errors. If
| error_reporting is disabled, the backtrace will not display, regardless
| of this setting
|
*/

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/

/* guard */
define('GUARD_CUSTOMER', 'api-customer');


//status code
define('METHOD_INVALID_STATUS', 405);
define('NOT_FOUND_STATUS', 404);
define('CANNOT_REMOVE_RESOURCE_STATUS', 409);
define('VALIDATION_FAIL', 422);

define('DEFAULT_CURRENT_PAGE', 1);
define('DEFAULT_PAGE', 10);

define('ACTIVE', 1);
define('INACTIVE', 2);
define('ADMIN_TYPE', 1);
define('CUSTOMER_TYPE', 2);

define('PENDING', 1);
define('APPROVE', 2);
define('REJECT', 3);
define('PAYING', 4);
define('DONE', 5);
define('WEEK', 1);
define('MONTH', 2);
define('YEAR', 3);

