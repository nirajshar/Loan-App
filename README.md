## Loan App - Credit Lending App

Loan App is designed for User to apply for Loan.
After through inspection of credit report of User Admin will Approve/Reject Application for Loan.
User has to repay Credit Debt by EWI (Every Week Installments) set with respect to Loan Amortization.
When all EWI (Every Week Installments) are paid by user, Loan will be set to closed.

## Modules

- **[User](#)**
    - There are 2 Types of User accessing the application User / Admin.
    - User can be registered from register API.
    - Admin is auto generated via DB:SEED.    

    - Constraints 
        - User can only apply/update Loan via Loan Application API.
        - User can only apply for Loan one at a time. Multiple applications at a time are restricted.
        - User can update their Loan Application when in SUBMITTED stage.
        - Admin can only approve/reject Loan via Loan Application API.

- **[Loan Application](#)**
    - Loan Application is used by User for Tendering for the Loan.

- **[Loan](#)**
    - Loan Application if approved then Loan is created for the Loan Application.

- **[Loan Amortization](#)**
    - Loan Application if approved then Loan Amortizations is created for the Approved Loan.
    - Loan are paid through EWI (Every Week Installments) via Loan Amortization API.

## Installation


```ps
composer install
```

create new .env file and edit database credentials there.

```ps
cp .env.example .env
```

Generate new app key.

```ps
php artisan key:generate
```

Run migrations

```ps
php artisan migrate
```

Run Seeder

```ps
php artisan db:seed
```

Run app

```ps
php artisan serve
```

Test

```ps
php artisan test
```

## License

The Loan App licensed under the [PROPREITER license](#).
