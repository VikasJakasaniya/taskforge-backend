# Quick Test Commands Reference

## Run All Tests
```bash
php artisan test
```

## Test Suites

### Run All Feature Tests
```bash
php artisan test --testsuite=Feature
```

### Run All Unit Tests
```bash
php artisan test --testsuite=Unit
```

## Feature Tests by Category

### Authentication Tests
```bash
# All auth tests
php artisan test tests/Feature/Auth/

# OTP Authentication tests (existing)
php artisan test tests/Feature/Auth/OtpAuthenticationTest.php

# Complete authentication flow tests (new)
php artisan test tests/Feature/Auth/AuthenticationFlowTest.php
```

### Task Tests
```bash
# All task tests
php artisan test tests/Feature/Task/

# Basic CRUD tests (existing)
php artisan test tests/Feature/Task/TaskCrudTest.php

# Advanced features tests (new)
php artisan test tests/Feature/Task/TaskAdvancedTest.php
```

### Import Tests
```bash
# Import progress tests (existing)
php artisan test tests/Feature/Import/ImportProgressTest.php
```
