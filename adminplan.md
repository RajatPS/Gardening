
# Plant Nursery & Garden Management Platform

# Admin & Staff Panel Project Plan

## Overview

This document defines the complete Admin Panel and Staff Panel architecture for the Plant Nursery & Garden Management Platform.

The Admin Panel must provide complete control over:

* Users
* Staff Members
* Products
* Orders
* Appointments
* Subscriptions
* Payments
* Reports
* Inventory
* AI Logs
* Notifications

All pages must support:

* Global Search
* Filtering
* Pagination
* Sorting
* Responsive Design
* Role-Based Access Control

---

# Panel Structure

## Admin URL

/admin

---

## Staff URL

/staff

---

# Authentication System

## Admin Login

Features:

* Email Login
* Password Login
* Remember Me
* Forgot Password

---

## Staff Login

Features:

* Email Login
* Password Login
* Remember Me
* Forgot Password

---

## Staff Registration

Accessible only by Admin.

Admin can:

* Create Staff
* Edit Staff
* Suspend Staff
* Delete Staff

Public users cannot create staff accounts.

---

# Sidebar Navigation

## Dashboard

## User Management

## Staff Management

## Product Management

## Order Management

## Appointment Management

## Subscription Management

## Transaction Records

## Inventory Management

## Notifications

## Reports & Analytics

## AI Logs

## Settings

## Profile

## Logout

---

# Dashboard

## Dashboard Homepage

Displays:

### Revenue Statistics

* Daily Revenue
* Weekly Revenue
* Monthly Revenue
* Yearly Revenue

### Sales Statistics

* Total Orders
* Pending Orders
* Completed Orders
* Cancelled Orders

### User Statistics

* Total Users
* Active Users
* Suspended Users

### Product Statistics

* Total Products
* Low Stock Products
* Out Of Stock Products

### Appointment Statistics

* Upcoming Visits
* Today's Visits
* Completed Visits

### Subscription Statistics

* Active Plans
* Expiring Plans
* Renewals

### Charts

* Revenue Chart
* Sales Chart
* User Growth Chart
* Subscription Growth Chart

---

# User Management

## User List Page

Table Columns:

* User ID
* Profile Image
* Full Name
* Email
* Phone
* Registration Date
* Status
* Actions

---

## User Search

Search By:

* Name
* Email
* Phone

---

## Filters

* Active Users
* Suspended Users
* New Users
* Subscription Users

---

## User Actions

### View User

### Edit User

### Activate User

### Deactivate User

### Delete User

### View Orders

### View Appointments

### View Subscription

### View Payment History

---

# Edit User Page

Editable Fields:

* Name
* Email
* Phone
* Address
* Profile Image
* Account Status

Buttons:

* Save
* Cancel

---

# Staff Management

## Staff List Page

Columns:

* Staff ID
* Name
* Email
* Phone
* Role
* Assigned Tasks
* Status

---

## Staff Actions

### Add Staff

### Edit Staff

### Suspend Staff

### Activate Staff

### Delete Staff

### Reset Password

### Assign Tasks

---

# Product Management

## Product List Page

Columns:

* Product ID
* Product Image
* Product Name
* Category
* Stock
* Price
* Status

---

## Product Search

Search By:

* Product Name
* SKU
* Category

---

## Product Filters

* In Stock
* Out Of Stock
* Low Stock
* Active
* Inactive

---

# Add Product Page

Fields:

* Product Name
* Product Slug
* Category
* Subcategory
* Brand
* SKU
* Price
* Sale Price
* Quantity
* Weight
* Dimensions

---

## Product Images

Supports:

* Multiple Image Upload
* Drag & Drop Upload
* Image Preview
* Image Reordering

---

## Product Details

* Description
* Benefits
* Care Instructions
* Watering Information
* Sunlight Information

---

## SEO Fields

* Meta Title
* Meta Description
* Keywords

---

## Product Status

* Active
* Draft
* Hidden

---

Buttons:

* Save Product
* Save Draft
* Cancel

---

# Edit Product Page

All product information must be editable.

Features:

* Replace Images
* Add Images
* Delete Images
* Update Product Information

Buttons:

* Update Product
* Delete Product

---

# Order Management

## Orders List Page

Columns:

* Order Number
* Customer Name
* Date
* Amount
* Payment Status
* Order Status

---

## Order Actions

### View Order

### Update Status

### Generate Invoice

### Cancel Order

### Delete Order

---

## Order Status Options

* Pending
* Processing
* Shipped
* Out For Delivery
* Delivered
* Cancelled
* Refunded

---

# Appointment Management

## Appointment List

Columns:

* Appointment ID
* Customer Name
* Service Type
* Date
* Assigned Staff
* Status

---

## Actions

* View
* Assign Staff
* Reschedule
* Complete
* Cancel

---

# Subscription Management

## Subscription List

Columns:

* User
* Plan
* Start Date
* End Date
* Status

---

## Actions

* Renew
* Upgrade
* Cancel
* View History

---

# Transaction Records

## Transaction Page

Purpose:

Store and monitor all payment records.

---

## Search

* Transaction ID
* User Name
* Order ID

---

## Filters

* Date Range
* Payment Method
* Payment Status
* User
* Amount Range

---

## View Transaction

Displays:

* Receipt
* Order Information
* Customer Information
* Payment Gateway Response

---

# Inventory Management

## Inventory Page

Displays:

* Current Stock
* Reserved Stock
* Sold Stock

---

## Inventory Actions

* Add Stock
* Reduce Stock
* Adjust Inventory

---

# Notifications

## Notification Center

Create:

* User Notifications
* Staff Notifications
* Promotional Notifications

Methods:

* Email
* SMS
* Push Notification

---

# Reports & Analytics

## Revenue Reports

## Sales Reports

## User Reports

## Subscription Reports

## Product Reports

## Staff Performance Reports

---

# AI Logs

Store:

* User AI Queries
* Plant Disease Requests
* AI Generated Reports

Admin can:

* Search Logs
* Filter Logs
* View AI Usage Statistics

---

# Settings

## General Settings

* Website Name
* Logo
* Contact Information

---

## Payment Settings

* Razorpay
* PhonePe
* UPI
* QR Payments

---

## Notification Settings

* Email
* SMS
* Push Notifications

---

# Search Requirements

Every management page must include:

* Search Bar
* Advanced Filters
* Sorting
* Pagination

Search must work without page refresh using AJAX.

---

# Backend Requirements

## Laravel

Controllers:

* UserController
* StaffController
* ProductController
* OrderController
* AppointmentController
* SubscriptionController
* TransactionController
* InventoryController
* ReportController
* NotificationController

---

## Middleware

* AdminMiddleware
* StaffMiddleware

---

## Permissions

Admin:

* Full Access

Staff:

* Limited Access

---

# Navigation Requirements

Every sidebar menu item must:

* Navigate correctly
* Highlight active page
* Preserve filters during navigation
* Support responsive mobile view

---

# UI Requirements

Use exactly the same:

* Color Palette
* Typography
* Border Radius
* Shadows
* Card Design
* Button Styles

as the Customer Panel.

Admin Panel and Customer Panel must look like parts of the same platform.

---

# Final Goal

Build an enterprise-grade Admin & Staff Management System with complete control over products, users, orders, subscriptions, appointments, payments, analytics, and AI services while maintaining a fast, secure, and professional user experience.
# Audit Log System

## Purpose

The Audit Log System records every important action performed by Admins and Staff members.

This ensures:

* Accountability
* Security
* Activity Tracking
* Easy Troubleshooting
* Fraud Prevention

---

# Audit Log Dashboard

## Sidebar Menu

Audit Logs

---

## Audit Log List Page

Table Columns:

* Log ID
* User Name
* User Role
* Action Type
* Module
* Old Value
* New Value
* IP Address
* Device Information
* Date & Time

---

# Actions To Record

## User Management

* User Created
* User Updated
* User Activated
* User Deactivated
* User Deleted

---

## Staff Management

* Staff Created
* Staff Updated
* Staff Suspended
* Staff Activated
* Staff Deleted
* Password Reset

---

## Product Management

* Product Added
* Product Updated
* Product Deleted
* Stock Increased
* Stock Reduced
* Product Status Changed

---

## Order Management

* Order Created
* Order Updated
* Order Cancelled
* Order Refunded
* Order Deleted
* Invoice Generated

---

## Subscription Management

* Subscription Created
* Renewed
* Upgraded
* Cancelled

---

## Appointment Management

* Appointment Created
* Rescheduled
* Assigned
* Completed
* Cancelled

---

## Inventory Management

* Stock Added
* Stock Removed
* Stock Adjusted

---

## Payment Management

* Payment Verified
* Payment Refunded
* Transaction Updated

---

## Settings

* Website Settings Updated
* Payment Settings Updated
* Notification Settings Updated

---

# Audit Log Filters

Search By:

* User Name
* Staff Name
* Action Type
* Module
* Date Range

---

## Advanced Filters

* Admin Actions
* Staff Actions
* Product Changes
* Order Changes
* Payment Changes

---

# Audit Log Detail Page

Displays:

### General Information

* Log ID
* Timestamp
* User
* Role
* IP Address

---

### Action Information

* Action Type
* Module Name
* Description

---

### Change Tracking

#### Previous Values

JSON View

#### Updated Values

JSON View

---

# Soft Delete System

Important:

No critical records should be permanently deleted.

Instead use:

Laravel Soft Deletes

Modules:

* Users
* Products
* Orders
* Staff
* Subscriptions

---

# Recycle Bin

## Sidebar Menu

Recycle Bin

---

## Recycle Bin Features

Displays:

* Deleted Users
* Deleted Products
* Deleted Orders
* Deleted Staff

Actions:

* Restore
* Permanently Delete

---

# Activity Timeline

## Dashboard Widget

Shows latest activities:

Examples:

* Staff A added Product X
* Admin updated User Y
* Staff B completed Appointment Z
* Order #1025 delivered

Updates in real time.

---

# System Monitoring

## Security Monitoring

Track:

* Failed Login Attempts
* Password Reset Requests
* Unauthorized Access Attempts
* Blocked Requests

---

## Login History

Track:

* Login Time
* Logout Time
* Browser
* Device
* IP Address

---

# Backup Management

## Sidebar Menu

Backup Management

---

## Features

* Manual Backup
* Scheduled Backup
* Database Backup
* File Backup
* Download Backup

---

# Super Admin Role

Highest permission level.

Can:

* Manage Admins
* Manage Staff
* View Audit Logs
* Access Security Settings
* Restore Deleted Data
* Manage Backups

---

# Recommended Roles

## Super Admin

Full Access

---

## Admin

Business Management Access

---

## Inventory Manager

Inventory + Products Only

---

## Customer Support

Users + Orders + Appointments

---

## Staff

Assigned Tasks Only

---

# Enterprise-Level Requirement

Every Create, Update, Delete, Restore, Status Change, Login, Logout, Payment Update, Inventory Update, and Configuration Change must be recorded automatically in the Audit Log System.
