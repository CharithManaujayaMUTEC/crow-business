# Existing Crow SMS integration

Do not remove the existing:

- Promotional Messages
- Send SMS
- SMS Logs
- SMS Settings
- Notify.lk credentials

The starter adds business-triggered SMS through:

`app/Services/Sms/NotifySmsService.php`

Map the existing SMS Settings screen to the `sms_settings` table, or adapt the service to read your current settings table if the existing project already has one.

Recommended business triggers:

1. Quotation sent -> optional SMS with quotation link.
2. Invoice issued -> optional SMS with invoice link.
3. Payment reminder -> scheduled SMS.
4. Overdue invoice -> scheduled SMS.
5. Payroll reminder -> internal/admin notification.

Keep the same Notify.lk account, API user ID, API key, sender ID and country code.

If the current Crow project already has a working Notify.lk API client, reuse that client instead of creating a second HTTP implementation.
