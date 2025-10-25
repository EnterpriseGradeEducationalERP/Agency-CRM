<?php
/**
 * Application Constants
 * Define all constants used throughout the application
 */

// User Roles
define('ROLE_ADMIN', 'admin');
define('ROLE_PROJECT_MANAGER', 'project_manager');
define('ROLE_SALES_EXECUTIVE', 'sales_executive');
define('ROLE_TEAM_MEMBER', 'team_member');
define('ROLE_FINANCE_OFFICER', 'finance_officer');
define('ROLE_CLIENT', 'client');

// Pipeline Stages
define('STAGE_LEAD', 'lead');
define('STAGE_CONTACTED', 'contacted');
define('STAGE_QUALIFIED', 'qualified');
define('STAGE_PROPOSAL_SENT', 'proposal_sent');
define('STAGE_NEGOTIATION', 'negotiation');
define('STAGE_WON', 'won');
define('STAGE_LOST', 'lost');

// Project Status
define('PROJECT_PLANNED', 'planned');
define('PROJECT_ACTIVE', 'active');
define('PROJECT_ON_HOLD', 'on_hold');
define('PROJECT_COMPLETED', 'completed');
define('PROJECT_CLOSED', 'closed');

// Task Status
define('TASK_TODO', 'todo');
define('TASK_IN_PROGRESS', 'in_progress');
define('TASK_BLOCKED', 'blocked');
define('TASK_DONE', 'done');

// Task Priority
define('PRIORITY_LOW', 'low');
define('PRIORITY_MEDIUM', 'medium');
define('PRIORITY_HIGH', 'high');
define('PRIORITY_URGENT', 'urgent');

// Quote Status
define('QUOTE_DRAFT', 'draft');
define('QUOTE_SENT', 'sent');
define('QUOTE_ACCEPTED', 'accepted');
define('QUOTE_REJECTED', 'rejected');
define('QUOTE_EXPIRED', 'expired');

// Invoice Status
define('INVOICE_DRAFT', 'draft');
define('INVOICE_SENT', 'sent');
define('INVOICE_PARTIALLY_PAID', 'partially_paid');
define('INVOICE_PAID', 'paid');
define('INVOICE_VOID', 'void');
define('INVOICE_OVERDUE', 'overdue');

// Payment Status
define('PAYMENT_PENDING', 'pending');
define('PAYMENT_SUCCESS', 'success');
define('PAYMENT_FAILED', 'failed');
define('PAYMENT_REFUNDED', 'refunded');

// Payment Methods
define('PAYMENT_RAZORPAY', 'razorpay');
define('PAYMENT_STRIPE', 'stripe');
define('PAYMENT_BANK_TRANSFER', 'bank_transfer');
define('PAYMENT_CASH', 'cash');
define('PAYMENT_CHEQUE', 'cheque');

// Notification Types
define('NOTIF_TASK_ASSIGNED', 'task_assigned');
define('NOTIF_TASK_UPDATED', 'task_updated');
define('NOTIF_PAYMENT_RECEIVED', 'payment_received');
define('NOTIF_DEADLINE_APPROACHING', 'deadline_approaching');
define('NOTIF_OVERDUE', 'overdue');
define('NOTIF_MEETING_REMINDER', 'meeting_reminder');

// Time Log Types
define('TIMELOG_BILLABLE', 'billable');
define('TIMELOG_NON_BILLABLE', 'non_billable');

// Client Sources
define('SOURCE_LEAD', 'lead');
define('SOURCE_REFERRAL', 'referral');
define('SOURCE_WEBSITE', 'website');
define('SOURCE_SOCIAL_MEDIA', 'social_media');
define('SOURCE_COLD_CALL', 'cold_call');
define('SOURCE_ADVERTISEMENT', 'advertisement');

// Currencies
define('CURRENCY_INR', 'INR');
define('CURRENCY_USD', 'USD');
define('CURRENCY_AED', 'AED');
define('CURRENCY_GBP', 'GBP');

// Tax Types
define('TAX_GST', 'GST');
define('TAX_VAT', 'VAT');
define('TAX_NONE', 'NONE');

// File Types
define('FILE_DOCUMENT', 'document');
define('FILE_IMAGE', 'image');
define('FILE_SPREADSHEET', 'spreadsheet');
define('FILE_OTHER', 'other');

// AI Models
define('AI_PRICING_ASSISTANT', 'pricing_assistant');
define('AI_RESOURCE_ALLOCATOR', 'resource_allocator');
define('AI_DEAL_SCORER', 'deal_scorer');
define('AI_INSIGHTS', 'insights');

// Default Values
define('DEFAULT_OVERHEAD_PERCENTAGE', 20);
define('DEFAULT_MARGIN_PERCENTAGE', 30);
define('DEFAULT_GST_PERCENTAGE', 18);
define('DEFAULT_OFFER_MULTIPLIER', 2.0);

// API Response Codes
define('HTTP_OK', 200);
define('HTTP_CREATED', 201);
define('HTTP_BAD_REQUEST', 400);
define('HTTP_UNAUTHORIZED', 401);
define('HTTP_FORBIDDEN', 403);
define('HTTP_NOT_FOUND', 404);
define('HTTP_UNPROCESSABLE', 422);
define('HTTP_SERVER_ERROR', 500);

// Pagination
define('DEFAULT_PAGE_SIZE', 20);
define('MAX_PAGE_SIZE', 100);

// Date Formats
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'd M Y');
define('DISPLAY_DATETIME_FORMAT', 'd M Y, h:i A');

