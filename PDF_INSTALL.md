# Crow PDF generation pack

## 1. Install DomPDF

From the Laravel project root:

```powershell
composer require barryvdh/laravel-dompdf
```

## 2. Copy this pack

Back up your current files first:

```powershell
Copy-Item ".\app\Filament\Resources" ".\app\Filament\Resources.backup-before-pdf" -Recurse -Force
```

Then copy the extracted `app` and `resources` folders into the Laravel project.

If you have custom changes in QuotationResource, InvoiceResource, PaymentResource or their Create pages, merge the PDF actions into your current files rather than blindly overwriting them.

## 3. Storage

Run:

```powershell
php artisan storage:link
```

## 4. Database fields

The PDF actions in this pack expect these optional path fields:

- quotations.pdf_path
- invoices.pdf_path
- payments.payment_slip_path

If these columns already exist, use them.

If they do not exist, create a migration:

```powershell
php artisan make:migration add_pdf_paths_to_business_documents_tables
```

Migration body:

```php
Schema::table('quotations', function (Blueprint $table) {
    $table->string('pdf_path')->nullable();
});

Schema::table('invoices', function (Blueprint $table) {
    $table->string('pdf_path')->nullable();
});

Schema::table('payments', function (Blueprint $table) {
    $table->string('payment_slip_path')->nullable();
});
```

Then:

```powershell
php artisan migrate
```

## 5. Important: persist generated paths

The service currently stores the generated PDF. To make the "Open PDF" buttons work reliably, update the service methods after storing:

Quotation:
```php
$path = $this->store(...);
$quotation->update(['pdf_path' => $path]);
return $path;
```

Invoice:
```php
$path = $this->store(...);
$invoice->update(['pdf_path' => $path]);
return $path;
```

Payment:
```php
$path = $this->store(...);
$payment->update(['payment_slip_path' => $path]);
return $path;
```

## 6. Test

```powershell
php artisan optimize:clear
php artisan serve
```

In Admin:

Quotations -> PDF
Invoices -> PDF
Payments -> Payment Slip

The generated documents are stored under:

storage/app/public/quotations/
storage/app/public/invoices/
storage/app/public/payment-slips/
