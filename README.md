
# OTP Verification System



A secure One-Time Password (OTP) verification system built with Laravel, Livewire, and Alpine.js. This system provides a seamless user experience while maintaining robust security measures for two-factor authentication.



## Installation



This project uses Laravel Sail for easy setup and development:


### Prerequisites

- Docker Desktop installed and running
- Git
- Composer - https://getcomposer.org/doc/00-intro.md



1. Clone the repository:

```bash

git  clone  https://github.com/alkxsd/laravel-otp-test

cd laravel-otp-test

```



2. Configure environment:

```bash

cp  .env.example  .env

```



3. Install dependencies and start Sail:

```bash

composer install



./vendor/bin/sail  up  -d

./vendor/bin/sail  artisan  key:generate

./vendor/bin/sail  artisan  migrate:fresh --seed

./vendor/bin/sail  npm  install

./vendor/bin/sail  npm  run  dev

```



The application will be available at `http://localhost` with Mailpit at `http://localhost:8025`.



## Testing with PEST


Run the test suite with:

```bash

./vendor/bin/sail  artisan  test

```



A separate testing environment(`.env.testing`) ensures reliable test execution.



## Assumptions

-  Email-based OTP delivery

- Logged-in state maintained during OTP verification

- 15-minute OTP expiration window

- Rate limiting for security with user experience in mind



## Additional Features

 - Implemented Mailpit for mocking OTP email notification

- Disabling OTP input when rate limit reached - **failure to enter correct OTP 5x**

-  Enhanced input handling with keyboard navigation and paste support

-  Automatic cleanup of expired OTPs

- Session regeneration after verification



## Technical Decisions



### Design Patterns

- Service Layer for OTP operations

- Data Transfer Objects for data handling

- Observer Pattern for notifications

- Repository-ready structure



### Security Implementation
- Utilizing Laravel's middleware in the route configuration to avoid brute force page visit via url bar

- Rate limiting at generation and verification

- Secure session handling

- Handle OTP expiration

- Expired token cleanup



### Frontend Architecture

- Livewire for real-time interactions

- Alpine.js for enhanced client-side functionality

- Mobile-first responsive design with Tailwind
