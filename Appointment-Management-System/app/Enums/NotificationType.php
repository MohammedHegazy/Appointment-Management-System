<?php

namespace App\Enums;

enum NotificationType: string
{
    case AppointmentReminder = 'appointment_reminder';
    case AppointmentConfirmed = 'appointment_confirmed';
    case AppointmentCancelled = 'appointment_cancelled';
    case PaymentReceipt = 'payment_receipt';
    case PaymentFailed = 'payment_failed';
    case AccountActivated = 'account_activated';
    case General = 'general';
}

